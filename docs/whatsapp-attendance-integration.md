# WhatsApp → FMS Driver Attendance

Supervisors send their drivers' daily attendance on WhatsApp. Those messages are read
automatically, turned into a **draft list inside FMS**, and only become real attendance
when a person reviews and confirms them.

Branch: `whatsapp`. Everything that does not need Meta credentials is built and
verified against the live database — see **What is built** at the end for what
is still outstanding.

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

### Why the review step is not optional

Three things in the live data mean some lines cannot be resolved by any amount
of parsing. They are the reason a person has to be in the loop:

| In the data today | What the reviewer sees |
|---|---|
| **9 vehicles carry two active drivers** (JZ-9641, JV-9148, JZ-9788, JZ-9782, JZ-9809, JV-8188, JZ-9790, LE-9920, NW-0009) | "2 drivers are on JZ-9641 — choose one", with the driver dropdown narrowed to those two |
| **`LE-9937` exists on two vehicle rows** | "2 vehicles share the number LE-9937" |
| **Some vehicles have no active driver** | "No active driver is assigned to JZ-8942" |

Guessing in any of these cases would mark the wrong man present. The row is
flagged instead and waits.

### One thing this screen will not do

A **Replace** line (`R`) is read and shown, but cannot be confirmed here. A
replace entry also needs the pool driver who stood in, and a WhatsApp line does
not carry that. Rather than save half a record, the row is refused with
"Replace needs a pool driver — mark this one on the Driver Attendance screen"
and stays pending. Every other status writes normally.

---

## 4. Database changes

| Change | Why |
|---|---|
| `whatsapp_senders` — name, whatsapp_number, station_id, is_active, notes | Only known numbers may post attendance. Kept separate from `users` because a supervisor is not necessarily a system user — there are only 6 users today |
| `whatsapp_messages` — wa_message_id (unique), from_number, whatsapp_sender_id, wa_profile_name, body, payload JSON, received_at, state, error | Audit trail. The unique id also means Meta's retries cannot create the same message twice |
| `whatsapp_attendance_drafts` — message_id, line_no, raw_line, attendance_date, vehicle_token, status_token, vehicle_id, driver_id, status, state, error, confirmed_by, confirmed_at, drivers_attendance_id | The review queue itself |
| A `whatsapp_attendance` row in `permissions`, granted to Admin | The menu item and the page are gated the same way every other module is |

`drivers_attendances` is not altered. Its existing columns already carry
everything an entry needs, and nothing is added to it.

### No unique index, for now

The plan originally called for a unique index on `drivers_attendances`
(`driver_id`, `date`). It has not been added, because the live table **already
holds 23 duplicate pairs** — 2026-08-20 has several drivers with two or three
rows each. Adding the index would fail outright.

This is a pre-existing data problem, not one WhatsApp introduces. Confirming a
draft uses `updateOrCreate` on (`driver_id`, `date`), so this module never adds
a duplicate of its own; it just cannot promise the table has none. Cleaning the
23 up is a separate decision, and the index can go on afterwards.

---

## 5. The code

| Piece | File |
|---|---|
| Config read from `.env` | `config/whatsapp.php` |
| Webhook (verify + receive, signature check, 200) | `app/Http/Controllers/WhatsAppWebhookController.php` |
| Parser — text only, no database, no HTTP | `app/Services/WhatsApp/AttendanceMessageParser.php` |
| Resolving a vehicle number to a driver | `app/Services/WhatsApp/AttendanceDraftBuilder.php` |
| Reading a message out of the request path | `app/Jobs/ParseWhatsAppAttendance.php` |
| Catch-up for anything that never got read | `app/Console/Commands/ParsePendingWhatsAppMessages.php` |
| Registering supervisors | `app/Console/Commands/AddWhatsAppSender.php` |
| Trying it without a Meta account | `app/Console/Commands/SimulateWhatsAppAttendance.php` |
| Review screen + confirm/reject | `app/Http/Controllers/WhatsAppAttendanceController.php` + `resources/views/whatsapp/attendance.blade.php` |
| Models | `app/Models/WhatsAppSender.php`, `WhatsAppMessage.php`, `WhatsAppAttendanceDraft.php` |
| Optional reply to supervisor | `app/Services/WhatsApp/WhatsAppClient.php` |
| Parser tests (23, no database) | `tests/Unit/AttendanceMessageParserTest.php` |

The parser and the resolver are deliberately separate. The parser decides that
a line says "JZ-9036 is present"; the resolver decides which driver that is.
Splitting them is what lets every parsing rule be tested without a schema.

### Where the parsing runs

By default, straight after the webhook response, in the same process
(`dispatchAfterResponse`). That needs no queue worker, which matters because
nothing currently runs `php artisan queue:work` — with a plain queued job the
drafts would simply never appear.

Set `WHATSAPP_PARSE_QUEUE=true` once a worker is running; the queue retries on
failure, which after-response dispatch cannot. Either way
`php artisan whatsapp:parse-pending` reads anything left behind, so a process
that died mid-request loses nothing.

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
- A driver at a different `station_id` from the supervisor is flagged on the review
  screen. **In practice this check is dormant:** 265 of the 271 drivers have no
  `station_id` at all, and the code will not flag what it does not know. It starts
  working as those are filled in; until then the sender list is the only gate.
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

### Today, with no Meta account

The whole flow except the webhook itself can be exercised now:

```
php artisan whatsapp:add-sender --list                        # stations and who is registered
php artisan whatsapp:add-sender "Imran Johar" 03001234567 --station=3
php artisan whatsapp:simulate --dry-run                       # sample message, nothing saved
php artisan whatsapp:simulate                                 # for real, then review in FMS
php artisan whatsapp:simulate --text="ATT 12-11-2026
JZ-9036 P
JZ-9019 A"
```

`whatsapp:simulate` writes the same `whatsapp_messages` row the webhook would and
queues the same drafts, so **Attendance > WhatsApp Attendance** fills up and the
confirm and reject buttons can be tried against real drivers.

### Once the credentials are in

- End-to-end on Meta's test number with your own phone first.
- Run one week alongside manual entry and compare, then switch over.

### Automated

- `php artisan test --filter=AttendanceMessageParserTest` — 23 parser tests, no
  database needed.

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

---

## 12. What is built

Everything that does not need a Meta credential is done, and checked against the
live database (all checks ran inside a transaction that was rolled back, so
nothing was written):

- The four migrations have run locally. **Note:** 51 older migrations in this
  repo are still unrecorded, so `php artisan migrate` on its own would try to
  recreate existing tables. These four were run with explicit `--path`
  arguments, and the same is needed on any other machine.
- 23 parser tests pass, covering lower case, missing spaces, numbered lines,
  `JZ-9036P` with no space, full words, numeric codes, both spellings of
  maintenance, Urdu `HAZRI`, four date formats, an unreadable date, a future
  date, Windows line endings, and closing chatter.
- The webhook refuses a missing, wrong, prefix-less or tampered signature
  (403), echoes the challenge on a correct verify token, refuses the wrong one
  (403), and returns 503 while `WHATSAPP_APP_SECRET` is unset. A redelivered
  message id does not create a second row; a delivery-status callback creates
  none.
- An unknown number is stored and flagged, never turned into drafts. Ordinary
  chat is marked ignored.
- Confirming wrote exactly the expected `drivers_attendances` rows, with the
  reviewer's corrections applied; confirming the same drafts twice wrote
  nothing more; rejecting wrote nothing at all.
- A truncated POST (PHP `max_input_vars`) is refused outright rather than
  saving part of a list.

### What is left

1. The four values from Meta, in `.env`.
2. The supervisor list — name, WhatsApp number, station — to seed
   `whatsapp_senders`. Until a number is in that table its messages are stored
   and flagged, not queued.
3. A webhook host: a tunnel to this machine, or `formulaone.pk`.
4. Pasting the webhook URL into the Meta dashboard and subscribing to
   **messages**.
5. The 23 duplicate `drivers_attendances` pairs, if the unique index is wanted.
