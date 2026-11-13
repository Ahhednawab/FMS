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

## 2. What the supervisors actually send

No format was imposed and none is going to be. These are the three shapes the
real groups use, transcribed from recordings of them, and the parser is written
around all three.

**A - the Orangi & Garden driver register**

```
Orengi & garden driver's attendence

1•JZ 9785 shahinsha 🅿️
6•LE 1940 Imran somro(m) Tanveer 🅿️
13•LE 1963 Nizam 🅰️ Imran akhtr 🅿️
14•LE 9927 A waseem & Habib 🅿️
```

A `•` after the index, a space inside the vehicle number, **the driver's name**,
and the status as the emoji 🅿️ or 🅰️ rather than a letter. Line 13 is an
absence and the man who covered it, on one line.

**B - another supervisor's driver register**

```
30=Jz-9641  per rehman
31=Jz-9641  Naveed
33=Jz-9744  Gachi (L)Shaid hussain
34=Jz-9676.  Saud
54=Lf-1068 Shahwazir( L)Shakeel
```

`=` instead of a bullet, a dash inside the number, a stray full stop after it,
and **no status marker at all** - the whole message is a present list. `(L)` is
a leave, followed by who stood in. The same vehicle appears twice when two
drivers share it. These run to 56 lines and more, past WhatsApp's "Read more".

**C - the vehicle register, a separate group**

```
12- NOV -2026 KORANGI
VEHICLES ATTENDANCE

1= Jz 9673 (P)
2= Jz 9741 (P
8= Jz 9853 inspection replecment LE-1940 (P)
14=Jz-9670  (Day)
15=Jz-9670  (Night)
22= Le. 5764 (P)
```

This one is about vehicles, not drivers, and goes to `vehicles_attendances`.
It carries a date in the header, puts the status in brackets, often misses the
closing one, and uses `(Day)` / `(Night)` for shifts rather than statuses. A
stand-in vehicle is named on the line.

### What the parser does with all this

| Rule | Why |
|---|---|
| The index prefix `1•`, `37=`, `23.`, `4)` is stripped | Every supervisor numbers differently |
| The vehicle is found by pattern - two letters, 3-4 digits, any separator | `JZ 9785`, `Jz-9641`, `Le. 5764` all reach the same key |
| A status marker applies to the text in front of it | That is what splits "Nizam 🅰️ Imran akhtr 🅿️" into two people |
| A bare letter counts only at the end of a line | Otherwise the initial in "A waseem & Habib" reads as Absent |
| No marker at all means Present, recorded as an assumption | Register B sends no markers, and the screen says so on every such row |
| A message is attendance when it holds three or more vehicle lines | No keyword is required, because the real registers carry none |

### The statuses

| Marker | Status | `attendance_status.id` |
|---|---|---|
| 🅿️ `(P)` `P` | Present | 1 |
| 🅰️ `(A)` `A` | Absent | 2 |
| `(L)` `L` | Leave | 3 |
| - | Replace | 4 |
| `(M)` | Under maintenance | 5 |
| `(I)` | Inspection | 6 |
| `(O)` `OFF` | OFF | 7 |

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

### The name decides, not the vehicle

This is the most important rule in the whole module, and it was not the
original plan.

Checked against 42 lines taken off the real recordings, **every one of the 56
vehicle numbers was found in the database** - but the driver the supervisor
named was frequently not the driver `drivers.vehicle_id` has on that vehicle:

```
Supervisor: JZ 9787 Adnan khan   FMS: Adnan Khan is on JZ-9668;
                                      JZ 9787 has Muhammad Yousuf
Supervisor: JZ 9739 naveed       FMS: Muhammad Naveed is on JZ-9641;
                                      JZ 9739 has Ahmed Faraz
Supervisor: JZ 9745 Talib        FMS: Talib Hussain is on JZ-9741;
                                      JZ 9745 has Shahid
```

Resolving by vehicle number alone would have marked Muhammad Yousuf present on
a day Adnan Khan drove. On a register that feeds payroll that is the one
failure that must not happen, so the name leads and the vehicle assignment is
the fallback. Where the two disagree the row still saves under the name, and
the screen says in red whom FMS had on that vehicle.

That the assignments have drifted is a finding in its own right, and worth
fixing separately - the WhatsApp feed is a good way to find them.

### Matching a name written on a phone

Supervisors write "shahinsha" for Shahin shah, "Juneed" for Junaid Ali,
"zeshan" for Muhammad Zeeshan, "Shabruk" for Shahrukh. `DriverNameMatcher`
compares by sound and by word rather than letter by letter: metaphone keys,
prefix shortenings, and a penalty for matching only part of a longer name.

It refuses as readily as it accepts. "Saud" scores 70% against its nearest
driver and is left for the reviewer; so are "Gachi" at 60%, "shakir" where two
drivers answer to it equally well, and "A waseem & Habib" where one line names
two men. A match only fills itself in above 78%, and the row always shows the
percentage it got.

### Why the review step is not optional

On top of the name drift, three things in the live data cannot be resolved by
any amount of parsing:

| In the data today | What the reviewer sees |
|---|---|
| **9 vehicles carry two active drivers** (JZ-9641, JV-9148, JZ-9788, JZ-9782, JZ-9809, JV-8188, JZ-9790, LE-9920, NW-0009) | The name usually settles it; where no name was sent, "2 drivers are on JZ-9641 - choose one" |
| **`LE-9937` exists on two vehicle rows** | "2 vehicles share the number LE-9937" |
| **Some vehicles have no active driver** | "No active driver is assigned to JZ-8942" |

### Replacements

A line that names a stand-in - "Nizam 🅰️ Imran akhtr 🅿️", or "Gachi (L)Shaid
hussain" - is saved the way the Driver Attendance screen saves one: a single
**Replace** row whose `driver_id` is the man who actually drove, with the man
he covered in `original_driver_id`. The two screens produce identical rows.

One gap to know about: the manual screen also re-syncs pool-driver assignments
for the date (`syncReplacementAssignmentsForDate`). The WhatsApp screen updates
driver availability through the shared `DriverAvailabilitySync`, but does not
run that second sweep. A Replace confirmed here is a correct attendance row; if
pool assignments matter for that day, open it on the Driver Attendance screen.

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
| Parser tests, all from real messages | `tests/Unit/AttendanceMessageParserTest.php` |
| Name-matching tests, every pair a real one | `tests/Unit/DriverNameMatcherTest.php` |

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
- 53 unit tests pass. The 19 parser tests are written from the real messages:
  bullets and equals signs, emoji statuses, a name between vehicle and status,
  an initial that must not read as Absent, an inline `(L)` with the stand-in
  after it, an unclosed bracket, `(Day)` and `(Night)`, a stray full stop, a
  replacement vehicle, and ordinary chat that must not be mistaken for a
  register. The 33 name-matching tests use real pairs in both directions -
  22 that must match, 7 that must not.
- Run end to end against the live database, all three formats: 56 of 56
  vehicle numbers resolved, names matched or refused as described above,
  confirmed rows written to `drivers_attendances` and `vehicles_attendances`
  including a Replace row and a stand-in vehicle in `pool_id`, and confirming
  the same drafts twice wrote nothing more.
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
