# WhatsApp → FMS Driver Attendance

Supervisors send their drivers' daily attendance on WhatsApp. This plan turns those
messages into rows in `drivers_attendances` automatically, instead of someone typing
them into the system by hand.

Branch: `whatsapp`. Nothing here is built yet — this is the agreed plan.

---

## 1. What it costs

Meta bills only the messages a **business sends**. Messages that **arrive** at our
number are not a billed category, so simply receiving attendance is free.

| Item | Cost |
|---|---|
| Receiving a supervisor's message | Free |
| Our confirmation reply (inside the 24-hour window) | First **1,000 per month per number** free |
| Replies beyond 1,000/month | Pakistan utility rate ≈ **$0.012** each |
| Cloud API access (direct from Meta) | Free |
| A BSP such as Twilio / 360dialog | ≈ $0.005 per message extra — avoided by going direct |

Realistic volume: 15 supervisors × 1 reply/day × 30 days ≈ **450 replies/month**, well
inside the free tier. In practice the running cost is zero.

Note: the 1,000-message free tier on service replies started 1 October 2026 (replies
were fully free before that). Confirm the Pakistan rate on Meta's official rate card
before budgeting — third-party figures disagree with each other.

---

## 2. Which phone number to use

A number can live **either** in the WhatsApp Business app **or** on the Cloud API —
never both. This is the one decision that has to be made before any code is written.

| | **Option A — new number** | **Option B — migrate the current number** |
|---|---|---|
| Supervisors | Must be told the new number | Nothing changes for them |
| Current WhatsApp Business app | Keeps working as it does today | **Stops working** — the number leaves the app |
| Risk to live communication | None | All chats on that number move to the API at once |
| Testing | Can build and test in parallel, safely | Testing happens on a live number |
| Old chat history | Stays in the app, untouched | Not carried over to the API |
| Human replies | The old number stays available for people | We must build an inbox inside FMS first |
| Extra cost | One SIM | None |
| Reversible | Yes — just stop using it | Possible, but a hassle |

### Recommendation: start with a new number

A dedicated "attendance number" is the safer and clearer choice:

- The number supervisors already use keeps working exactly as it does today — no risk
  to the client's day-to-day communication.
- Supervisors learn one simple rule: *attendance goes to this number, everything else
  to the old one.* That also keeps attendance messages out of a busy inbox.
- We can build and test the whole flow without touching anything live.
- If later it turns out one number is preferable, the old one can still be migrated —
  the reverse (un-migrating mid-project) is far more painful.

Before any SIM is bought, Meta gives a **free test number** on the developer app. All
development and the first round of testing happen on that, at no cost.

If the client insists on a single number, Option B works — but then the FMS inbox
(step 7 below) must be finished *before* migration, otherwise nobody can read or reply
to anything sent to that number.

---

## 3. How the flow works

```
Supervisor's WhatsApp
        │  "ATT 12-11-2026 / JZ-9036 P / JZ-9019 A"
        ▼
Meta Cloud API  ──POST──►  https://formulaone.pk/webhooks/whatsapp
                                 │ verify signature, return 200 immediately
                                 ▼
                           whatsapp_messages  (raw payload stored)
                                 │ queued job
                                 ▼
                           parser → validate → drivers_attendances
                                 │
                                 ▼
                           reply: "12 saved, 2 errors: ..."
```

Returning `200` straight away matters: if Meta does not get it, the same message is
delivered again and again.

---

## 4. Message format

Free text is too unreliable to parse. Supervisors use one fixed shape:

```
ATT 12-11-2026
JZ-9036 P
JZ-9019 A
LE-9912 OFF
```

- First line: `ATT` plus the date. Date may be omitted — then today is used.
- Each following line: vehicle number, then a status code.

Status codes map to the existing `attendance_status` table:

| Code | Status | `drivers_attendances.status` |
|---|---|---|
| `P` | Present | 1 |
| `A` | Absent | 2 |
| `L` | Leave | 3 |
| `R` | Replace | 4 |
| `M` | Under maintenance | 5 |
| `I` | Inspection | 6 |
| `OFF` | OFF | 7 |

Vehicle numbers are matched case-insensitively and with spaces/dashes normalised, so
`jz 9036` and `JZ-9036` both resolve. The driver comes from `drivers.vehicle_id`.

A later improvement is a WhatsApp interactive list, where the supervisor taps statuses
instead of typing. Inside the 24-hour window that is still a free service message.

---

## 5. Database changes

| Change | Why |
|---|---|
| New table `whatsapp_messages` (wa_message_id, from_number, body, payload JSON, status, parsed_at, error) | Audit trail; lets a failed message be re-processed instead of lost |
| New table `whatsapp_senders` (name, whatsapp_number, station_id, is_active) | Only known numbers may post attendance. Kept separate from `users` because a supervisor is not necessarily a system user — there are only 6 users today |
| Unique index on `drivers_attendances` (`driver_id`, `date`) | The table has **no** unique index today, so a supervisor sending twice would create duplicate rows. Writes become `updateOrCreate` |

Existing columns are enough otherwise: `driver_id`, `vehicle_id`, `date`, `status`,
`is_active` already carry everything a WhatsApp entry needs.

---

## 6. Steps — Meta side

1. Meta Business Suite account, then **Business Verification** (company documents).
2. developers.facebook.com → **Create App** → type *Business* → add the **WhatsApp** product.
3. Create the WhatsApp Business Account (WABA); start on the free **test number**.
4. **Permanent token**: Business Settings → System Users → assign the WABA asset →
   generate a token with `whatsapp_business_messaging` and `whatsapp_business_management`.
   (The 24-hour token shown in the dashboard is for testing only.)
5. Register the real number when the flow is proven — new SIM (Option A) or migration
   (Option B).

Credentials go in `.env` (`WHATSAPP_TOKEN`, `WHATSAPP_PHONE_ID`, `WHATSAPP_APP_SECRET`,
`WHATSAPP_VERIFY_TOKEN`) and are never committed — `.env` and `.env.*` are gitignored.

## 7. Steps — FMS side

6. Route `GET|POST /webhooks/whatsapp`:
   - `GET` — Meta's verification handshake: check `hub.verify_token`, echo `hub.challenge`.
   - `POST` — verify the `X-Hub-Signature-256` HMAC against the app secret, store the
     payload, return `200`, and hand the work to a queued job.
   - Exclude the route from CSRF in `bootstrap/app.php` (`validateCsrfTokens except`).
   - Queue is already `database`, so no new infrastructure is needed.
7. **WhatsApp Inbox page** in FMS: incoming messages, what the parser made of each one,
   and a way to correct a bad line by hand. Without this, a mistyped message disappears
   silently — and under Option B this screen is also the only way to read that number.
8. Parser + writer: resolve vehicle → driver, validate the status code, then
   `updateOrCreate` on (`driver_id`, `date`).
9. Reply with one summary message: `12 saved · 2 errors: JZ-0000 not found`.

## 8. Security rules

- Every request must pass the `X-Hub-Signature-256` check — without it anyone who finds
  the URL can post attendance.
- Only numbers listed in `whatsapp_senders` are accepted; everything else is stored and
  flagged, never applied.
- A supervisor may only mark drivers at their own `station_id`.
- Unofficial libraries (`whatsapp-web.js`, Baileys) can read a normal WhatsApp account,
  but they breach WhatsApp's terms and the number can be banned permanently. Not an
  option for a client system.

## 9. Testing

- Unit-test the parser against real supervisor messages, including messy ones:
  lower case, extra spaces, unknown vehicle, duplicate line, wrong date.
- End-to-end on Meta's test number before any live number is touched.
- Run one week in parallel with manual entry and compare the two, then switch over.

---

## 10. Still to decide

- **New number or migration** — recommendation above is a new, dedicated number.
- Who the supervisors are and which station each one covers (fills `whatsapp_senders`).
- Whether a confirmation reply is wanted for every message, or only when there are errors.
