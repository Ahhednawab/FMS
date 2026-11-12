# WhatsApp → FMS Driver Attendance

Supervisors send their drivers' daily attendance on WhatsApp. Those messages are read
automatically, turned into a **draft list inside FMS**, and only become real attendance
when a person reviews and confirms them.

Branch: `whatsapp`. Nothing is built yet — this is the agreed plan.

---

## 1. The flow

```
Supervisor's WhatsApp
        │  "ATT 12-11-2026 / JZ-9036 P / JZ-9019 A"
        ▼
Meta Cloud API  ──POST──►  webhook  (verify signature, return 200 at once)
                                 │
                                 ▼
                     whatsapp_messages        raw message kept as-is
                                 │ queued job
                                 ▼
                 whatsapp_attendance_drafts   one row per line, driver resolved
                                 │
                                 ▼
                  ┌──────────────────────────────┐
                  │  FMS → WhatsApp Attendance    │   ← a person checks it here
                  │  review, correct, confirm     │
                  └──────────────────────────────┘
                                 │ Confirm
                                 ▼
                       drivers_attendances      only now is it real
```

Nothing reaches `drivers_attendances` on its own. A wrong vehicle number, a typo, a
supervisor sending twice — all of it is caught on the review screen first.

Returning `200` immediately matters: if Meta does not get it, the same message is
delivered again and again.

---

## 2. Message format

Free text is too unreliable to parse. Supervisors use one fixed shape:

```
ATT 12-11-2026
JZ-9036 P
JZ-9019 A
LE-9912 OFF
```

**First line** — `ATT` (or `ATTENDANCE`), then the date. If the date is left out,
today is used. `12-11-2026`, `12/11/2026` and `2026-11-12` are all accepted.

**Every line after that** — vehicle number, then a status code.

| Code | Status | `drivers_attendances.status` |
|---|---|---|
| `P` | Present | 1 |
| `A` | Absent | 2 |
| `L` | Leave | 3 |
| `R` | Replace | 4 |
| `M` | Under maintenance | 5 |
| `I` | Inspection | 6 |
| `OFF` | OFF | 7 |

The parser is deliberately forgiving: upper or lower case, extra spaces, and missing or
extra dashes all work, so `jz 9036 p` and `JZ-9036 P` are the same thing. Full words
(`Present`, `Absent`) are accepted too. The vehicle is matched first on
`vehicles.vehicle_no`; if that fails, on the driver's `serial_no`. The driver comes
from `drivers.vehicle_id`.

Anything the parser cannot make sense of is **not discarded** — it lands on the review
screen marked with the problem, so it can be fixed by hand.

---

## 3. The review screen

A new page under Attendance: **WhatsApp Attendance**.

- Filters: date, supervisor, and state (pending / confirmed / rejected).
- One row per attendance line, showing: supervisor, date, exactly what was typed, the
  driver and vehicle it resolved to, and the status as a **dropdown that can be changed**.
- Rows that failed to resolve are flagged with the reason (unknown vehicle, bad status
  code, no driver on that vehicle).
- If attendance already exists for that driver and date, the row warns that confirming
  will overwrite it, and shows the current value.
- Tick rows → **Confirm Selected** (writes them) or **Reject Selected** (closes them).

Confirming writes with `updateOrCreate` on (`driver_id`, `date`), records who confirmed
it and when, and links the draft to the row it created.

---

## 4. Database changes

| Change | Why |
|---|---|
| `whatsapp_senders` — name, whatsapp_number, station_id, is_active | Only known numbers may post attendance. Kept separate from `users` because a supervisor is not necessarily a system user — there are only 6 users today |
| `whatsapp_messages` — wa_message_id (unique), from_number, body, payload JSON, received_at, state, error | Audit trail. The unique id also means Meta's retries cannot create the same message twice |
| `whatsapp_attendance_drafts` — message_id, date, raw_line, vehicle_id, driver_id, status, state, error, confirmed_by, confirmed_at, drivers_attendance_id | The review queue itself |
| Unique index on `drivers_attendances` (`driver_id`, `date`) | The table has **no** unique index today, so the same driver could get two rows for one day |

Existing columns cover the rest: `driver_id`, `vehicle_id`, `date`, `status` and
`is_active` already carry everything an entry needs.

---

## 5. What gets built

| Piece | File |
|---|---|
| Config read from `.env` | `config/whatsapp.php` |
| Webhook (verify + receive) | `app/Http/Controllers/WhatsAppWebhookController.php` |
| Signature check, 200, queue | same controller |
| Parser (no HTTP, unit-testable) | `app/Services/WhatsApp/AttendanceMessageParser.php` |
| Background parsing | `app/Jobs/ParseWhatsAppAttendance.php` |
| Review screen + confirm/reject | `app/Http/Controllers/WhatsAppAttendanceController.php` + `resources/views/whatsapp/attendance.blade.php` |
| Optional reply to supervisor | `app/Services/WhatsApp/WhatsAppClient.php` |
| Parser tests | `tests/Unit/AttendanceMessageParserTest.php` |

The queue is already `database`, so no new infrastructure is needed.

---

## 6. Which phone number

A number lives **either** in the WhatsApp Business app **or** on the Cloud API — never
both.

| | **New number** | **Migrate the current number** |
|---|---|---|
| Supervisors | Must be told the new number | Nothing changes for them |
| Current WhatsApp Business app | Keeps working | **Stops working** — the number leaves the app |
| Risk to live communication | None | All chats on that number move to the API at once |
| Old chat history | Stays in the app | Not carried over |
| Reversible | Yes | Possible, but a hassle |

**Recommendation: a new, dedicated attendance number.** The number the client uses today
keeps working untouched, supervisors get one clear rule, and the decision stays open —
the old number can still be migrated later.

If a single number is required, migration works, but the review screen must be finished
**before** migrating, because after migration nobody can read that number from a phone.

---

## 7. Development on Meta's test number

Development starts on Meta's free test number — no SIM and no business verification
needed to begin.

Its limits, which shape the pilot:

- Up to **5 recipient numbers**, each verified with a code in WhatsApp. So the pilot
  runs with your own number plus three or four supervisors.
- The webhook must be **HTTPS with a valid TLS certificate**; a self-signed certificate
  is not accepted. Locally that means a tunnel (ngrok or cloudflared) in front of
  `localhost:8000`; in production it is `https://formulaone.pk`.
- Business verification and a real number are only needed to go past those 5 numbers.

---

## 8. Security

- Every request must pass the `X-Hub-Signature-256` check — without it, anyone who finds
  the URL could post attendance.
- Only numbers in `whatsapp_senders` are accepted; anything else is stored and flagged,
  never turned into a draft.
- A supervisor may only mark drivers at their own `station_id`.
- Credentials (`WHATSAPP_TOKEN`, `WHATSAPP_PHONE_ID`, `WHATSAPP_APP_SECRET`,
  `WHATSAPP_VERIFY_TOKEN`) live in `.env` only. `.env` and `.env.*` are gitignored and
  nothing goes in the repo.
- Unofficial libraries (`whatsapp-web.js`, Baileys) can read a normal WhatsApp account,
  but they breach WhatsApp's terms and the number can be banned permanently. Not an
  option for a client system.

---

## 9. Who does what

**On the Meta side (needs your login and documents):**

1. Create a Meta Business Portfolio at business.facebook.com.
2. developers.facebook.com → **Create App** → type *Business* → add the **WhatsApp**
   product. A test number and a 24-hour token appear automatically.
3. Under WhatsApp → API setup, add and verify the recipient numbers (max 5): your own,
   and a few supervisors.
4. Copy **Phone number ID**, **WhatsApp Business Account ID**, **App Secret** and the
   temporary token straight into `.env` — not into chat.
5. Choose any random string as `WHATSAPP_VERIFY_TOKEN`.
6. Decide the webhook host: a tunnel to your machine, or deploy to `formulaone.pk`.
7. Collect the supervisor list — name, WhatsApp number, station.
8. Once the code is in place, paste the webhook URL and verify token into the Meta
   dashboard, click Verify, and subscribe to the **messages** field.

**On the code side:** everything in section 5. The migrations, parser, review screen and
tests do not need any Meta credentials, so they can be built while the account is being
set up.

---

## 10. Testing

- Unit-test the parser on real supervisor messages, including messy ones: lower case,
  extra spaces, unknown vehicle, duplicate line, wrong date, missing status.
- End-to-end on the test number with your own phone first.
- Run one week alongside manual entry and compare, then switch over.

---

## 11. What it costs

Meta bills only messages a **business sends**. Messages that arrive are not a billed
category, so receiving attendance is free.

| Item | Cost |
|---|---|
| Receiving a supervisor's message | Free |
| A confirmation reply inside the 24-hour window | First **1,000 per month per number** free |
| Replies beyond 1,000/month | Pakistan utility rate ≈ **$0.012** each |
| Cloud API direct from Meta | Free |
| A BSP such as Twilio / 360dialog | ≈ $0.005 per message extra — avoided by going direct |

At 15 supervisors × 1 reply/day the running cost is effectively zero. Confirm the
Pakistan rate on Meta's official rate card before budgeting — third-party figures
disagree with each other.
