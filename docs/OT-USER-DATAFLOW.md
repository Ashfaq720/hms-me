# OT Management — User Data Flow Walkthrough

This document shows **exactly what data to enter at every step** to take a patient from initial surgery request all the way to billing closure. We use one worked example throughout: an **appendectomy for an IPD patient**.

> 📝 Replace the example values (patient name, dates, doctor names) with your own.

---

## Table of Contents

- [Phase 0 — One-Time Master Setup](#phase-0--one-time-master-setup)
- [Phase 1 — Surgery Request](#phase-1--surgery-request)
- [Phase 2 — Review & Approval](#phase-2--review--approval)
- [Phase 3 — Scheduling](#phase-3--scheduling)
- [Phase 4 — Pre-Operative Clearance](#phase-4--pre-operative-clearance)
- [Phase 5 — Patient Transfer to OT](#phase-5--patient-transfer-to-ot)
- [Phase 6 — Anesthesia](#phase-6--anesthesia)
- [Phase 7 — Surgery Execution](#phase-7--surgery-execution)
- [Phase 8 — Consumables Recording](#phase-8--consumables-recording)
- [Phase 9 — Post-Operative Notes](#phase-9--post-operative-notes)
- [Phase 10 — Recovery / PACU](#phase-10--recovery--pacu)
- [Phase 11 — Transfer Back to Ward](#phase-11--transfer-back-to-ward)
- [Phase 12 — Cleaning & Sterilization](#phase-12--cleaning--sterilization)
- [Phase 13 — Inventory Deduction](#phase-13--inventory-deduction)
- [Phase 14 — Billing](#phase-14--billing)
- [Reports & Audit Trail](#reports--audit-trail)

---

## Phase 0 — One-Time Master Setup

Before any surgery can be requested, an admin must configure the masters. **Do this once** when setting up the module.

### Step 0.1 — Seed sample masters (fastest)

```bash
php artisan db:seed --class=OtSampleDataSeeder
```

This creates: 5 rooms, 11 equipment, 5 categories, 6 anesthesia types, 8 surgery types, 15 consumables.

### Step 0.2 — Or manually configure

Navigate to **OT Management → OT Setup / Masters** and fill each:

| Master | Sample entry |
|---|---|
| **OT Rooms** | Code `OT-01`, Name `Major OT 1`, Type `Major`, Floor (pick from list), Block `Surgical Block`, Status `available`, ☑ Active |
| **Equipment** | Code `EQ-LAP-01`, Name `Laparoscopy Tower`, Category `Endoscopy`, Status `available`, ☑ Active |
| **Surgery Categories** | Name `Major Surgery`, Code `MAJOR`, ☑ Active |
| **Anesthesia Types** | Name `General Anesthesia`, Code `GA`, ☑ Active |
| **Surgery Types** | Name `Appendectomy`, Category `Major Surgery`, Duration `90` min, Standard charge `20000`, Surgeon fee `8000`, Anesthesia fee `4000`, OT room charge `5000`, Recovery charge `1500` |
| **Consumables** | Name `Surgical Gloves (Sterile)`, Code `CON-GLV-01`, Type `consumable`, Unit `pair`, Rate `80`, Current stock `500`, Reorder level `50` |

### Step 0.3 — Blood Group master (required if any surgery needs blood)

Navigate to `/blood-bank/blood-groups` and add `A+`, `A-`, `B+`, `B-`, `AB+`, `AB-`, `O+`, `O-` if not already there.

### Step 0.4 — Doctor unavailability (optional but recommended)

Navigate to `/ot/doctor-unavailability` (or add via API) — register doctors' leave / on-call / OPD windows so scheduling conflicts are caught automatically.

---

## Phase 1 — Surgery Request

**Who:** Attending doctor (consultant or junior)
**Where:** `/ot/surgery-requests/create`
**Trigger:** Doctor decides patient needs surgery

### Step 1.1 — Open the form

Click **OT Management → Surgery Request** in sidebar, then click the blue **+ New Surgery Request** button.

### Step 1.2 — Fill the form (use these exact values for the walkthrough)

#### Patient & Encounter

| Field | Example value |
|---|---|
| Patient * | `John Doe (MRN-000123)` (search) |
| Encounter Type * | `IPD` |
| Encounter ID | (leave blank if linked through IPD admission) |
| IPD Admission ID | `15` (the patient's active admission) |

#### Surgery / Procedure

| Field | Example value |
|---|---|
| Surgery Category | `Major Surgery` |
| Surgery / Procedure | `Appendectomy` — duration `90 min` auto-fills |
| Department | `General Surgery` |
| Required OT Type | `General OT` |
| Requesting Doctor | `Dr. Smith` |
| Primary Surgeon | `Dr. Karim` (leave same as requesting if same person) |

#### Diagnosis & Clinical

| Field | Example value |
|---|---|
| Primary Diagnosis * | `Acute appendicitis with right lower abdominal pain` |
| Secondary Diagnosis | (blank) |
| ICD-10 Code | `K35.9` |
| ASA Grade | `II` |
| Clinical Indication | `Patient presented with 24-hour history of severe RLQ pain, fever 38.5°C, raised WBC count` |
| Procedure Notes | `Open or laparoscopic depending on intra-op findings` |

#### Priority & Date

| Field | Example value |
|---|---|
| Priority | `Urgent` (or `Emergency` if immediate) |
| Preferred Date | (tomorrow's date) |
| Preferred Time | `10:00` |
| Duration (min) | `90` (auto-filled) |
| Date Flexibility | `Fixed` |
| Reason for Preferred Date | `OT slot available and patient fasting from midnight` |

> 🚨 If you set Priority = `Emergency`, the Emergency Details block appears and the **Emergency Reason** field becomes required.

#### Blood Arrangement (typically NO for appendectomy)

| Field | Example value |
|---|---|
| ☐ Blood Required | (leave unchecked for routine appendectomy) |
| Units | (blank) |
| Blood Group | (blank) |
| Components | (blank) |
| ☐ Crossmatch Required | (unchecked) |
| Blood Bank Instructions | (blank) |

#### Required Equipment

Click **+ Add Equipment** twice:

| Row | Equipment | Qty | ☑ Mandatory | Setup Instruction |
|---|---|---|---|---|
| 1 | `Laparoscopy Tower` | `1` | ✓ | `Calibrate before use` |
| 2 | `Diathermy / Electrocautery` | `1` | ✓ | (blank) |

#### Approval & Instructions

| Field | Example value |
|---|---|
| ☑ Junior approval required | (if you're an intern) |
| ☑ Consultant approval required | (if junior is submitting) |
| Special Instructions | `Keep patient fasting from midnight. Diabetic — check sugar at 6 AM.` |

### Step 1.3 — Submit

Click **Submit Request** (or **Save Draft** to come back later).

✅ **Outcome:** Request gets a number like `OTR-2026-000125`. Status = `Submitted`. OT Coordinator + Anesthetist are notified.

---

## Phase 2 — Review & Approval

**Who:** OT Coordinator (+ Junior / Consultant if hierarchical approval was set)
**Where:** `/ot/surgery-requests`
**Trigger:** New request appears in the list

### Step 2.1 — Open the submitted request

Find `OTR-2026-000125` in the list (filter by Status = `Submitted`) and click 👁 View.

### Step 2.2 — Grant approvals (if required)

Scroll to the **Hierarchical Approvals** card:
- Junior approval → click **Grant** → confirmation
- Consultant approval → click **Grant** (only enabled after junior is done)

### Step 2.3 — Start review

Click **Start Review** at the top — status moves to `Under Review`.

### Step 2.4 — Pick one action

| Action | When to use | Required input |
|---|---|---|
| **Accept** | Everything's fine, ready for scheduling | (nothing) |
| **Send Back** | Needs correction by requesting doctor | Reason: `Surgical site not specified — please indicate left or right` |
| **Pending Information** | Waiting on external info | Reason: `Awaiting cardiac evaluation report` |
| **Reject** | Won't proceed | Reason: `Patient declined surgery` |
| **Fast-Track** (emergency only) | Skip normal flow | Reason: `Suspected ruptured appendix — immediate OT` |
| **Cancel** | Withdraw at any non-terminal point | Reason: `Patient discharged at own request` |

For our walkthrough → click **Accept**.

### Step 2.5 — Move to scheduling

Click **Move to Scheduling**. Status → `Moved to Scheduling`.

A green **Schedule** button now appears.

---

## Phase 3 — Scheduling

**Who:** OT Coordinator
**Where:** Click **Schedule** on the request, or **OT Management → Surgery Scheduling → + New Schedule**

### Step 3.1 — Fill the schedule form

| Field | Example value |
|---|---|
| Surgery Request | `OTR-2026-000125 — John Doe (Appendectomy)` (auto-selected) |
| OT Room | `Major OT 1 (OT-01)` |
| Start | (tomorrow) `10:00` |
| End | `11:30` (auto-calculated from 90 min duration) |
| Cleaning Buffer (min) | `30` (room blocked until 12:00) |
| ☐ Emergency Fast-Track | (unchecked for routine) |

### Step 3.2 — Add OT team rows

| Role | Staff | Type | Specialization |
|---|---|---|---|
| `Primary Surgeon` | `Dr. Karim` | `doctor` | (blank) |
| `Anesthetist` | `Dr. Hassan` | `doctor` | (blank) |
| `Scrub Nurse` | `Nurse Aisha` | `user` | (blank) |
| `Circulating Nurse` | `Nurse Beatrice` | `user` | (blank) |
| `Technician` | `Tech Carlos` | `user` | `Laparoscopy` |

### Step 3.3 — Select equipment

Multi-select from the dropdown: `Laparoscopy Tower`, `Diathermy / Electrocautery`, `OT Light (LED)`, `Operating Table`, `Suction Unit`.

### Step 3.4 — Submit

Click **Schedule Surgery**.

**System checks:**
- ✅ Room not double-booked
- ✅ Each staff member free at this time
- ✅ No doctor on leave/on-call for this slot
- ✅ Equipment available

If conflict found, you get a specific message like:
> ❌ Primary Surgeon (id 5): doctor is leave (May 19 09:45 – May 19 13:45)

Resolve and retry.

✅ **Outcome:** Schedule number `SS-000045` created. Status = `Scheduled`. Team + equipment notified.

---

## Phase 4 — Pre-Operative Clearance

**Who:** IPD Nurse + Anesthetist + Surgeon (in parallel)
**Where:** `/ot/pre-op`
**Trigger:** Schedule created — checklist auto-generated

### Step 4.1 — Find the case

Filter by `Date` = (tomorrow's date) — `SS-000045` appears with `0% complete` progress bar.

Click 👁 to open the checklist.

### Step 4.2 — Tick items as they're completed

| Item | Who confirms | When |
|---|---|---|
| ☑ Consent Obtained | Nurse | Patient/family signs `OT-CONSENT-FORM` (upload PDF in Documents) |
| ☑ Lab Tests Completed | Doctor reviewing | CBC, blood group, RBS, creatinine — review in Lab module |
| ☑ Radiology Completed | Doctor reviewing | USG abdomen reviewed |
| ☑ NPO / Fasting Confirmed | Nurse | Patient fasting since midnight ✓ |
| ☑ Blood Arranged | Nurse (skip if blood not required) | N/A for this case |
| ☑ Allergies Reviewed | Anesthetist | "No known allergies" — confirmed |
| ☑ Vitals Recorded | Nurse | BP `120/80`, Pulse `78`, Temp `37.2°C`, RR `16`, SpO₂ `98%` |
| ☑ Anesthesia Clearance | Anesthetist | ASA II, fit for GA |
| ☑ Doctor Clearance | Surgeon | Surgeon confirms patient ready |
| ☑ Nurse Confirmation | Nurse | Final check — identity, IV line, gown, jewelry removed |
| (optional) ☑ Site Marked | Surgeon | Right iliac fossa marked with marker |
| (optional) ☑ ID Band Verified | Nurse | Wristband scanned/checked |

Progress bar shows `100%` when all 10 required items ticked.

### Step 4.3 — Mark Complete

Click **Mark Complete (Ready for OT)** button.

> 🚨 If any required item is unchecked, the button does nothing. To override (emergency only), click **Emergency Override** and enter a reason — fully audited.

✅ **Outcome:** Schedule status → `Ready for OT`. OT Nurse + Surgeon + Anesthetist notified.

---

## Phase 5 — Patient Transfer to OT

**Who:** IPD Nurse + Porter
**Where:** Schedule detail page (`/ot/schedules/45`) or `/ot/transfers`
**Trigger:** Patient is `Ready for OT`

### Step 5.1 — Initiate transfer

On the schedule detail page, click **Transfer to OT**. Enter:

| Field | Example value |
|---|---|
| Direction | `to_ot` |
| From | `IPD Ward — Bed 12` |
| To | `Major OT 1` (auto) |
| Porter ID | `45` (the porter's user ID) |
| Nurse ID | `78` |
| Notes | `Patient stable, IV in left hand, oxygen not required` |

Click **Initiate Transfer**.

> 🚨 If pre-op checklist isn't marked complete, the system blocks the transfer with a clear error message.

✅ **Outcome:** Status → `Transfer Started`. A transfer record is created with `initiated_at = now()`.

### Step 5.2 — Patient arrives at OT

When the patient physically enters the OT, the OT Nurse clicks **Mark Arrived** on the transfer record.

✅ **Outcome:** Status → `Patient Received in OT`. OT room state → `Patient Received`.

---

## Phase 6 — Anesthesia

**Who:** Anesthetist
**Where:** `/ot/anesthesia/45`
**Trigger:** Patient received in OT

### Step 6.1 — Open the anesthesia record

From the schedule, click **Anesthesia** tab.

### Step 6.2 — Fill the assessment fields

| Field | Example value |
|---|---|
| Anesthesia Type | `General Anesthesia` |
| Anesthetist | `Dr. Hassan` (anesthetist_id) |
| Pre-Anesthesia Assessment | `Mallampati II, mouth opening adequate, no loose teeth, NPO 10 hrs` |
| Drugs Used | `Propofol 100mg IV, Fentanyl 100mcg IV, Atracurium 30mg IV. Maintenance: Sevoflurane 1.5%, Air/O₂ 50:50` |
| Airway Management | `Endotracheal tube size 7.5, easy intubation, single attempt, tube fixed at 22cm` |
| Complications | (blank) |
| ASA Grade | `II` |

Click **Save**.

### Step 6.3 — Start anesthesia

Click **Start Anesthesia** button. Status → `Anesthesia Started`. `Induction time = now()` is stamped.

---

## Phase 7 — Surgery Execution

**Who:** Surgeon (with scrub nurse support)
**Where:** `/ot/intra-op/45`

### Step 7.1 — Start surgery

Click **Start Surgery** button (only enabled after anesthesia started).

✅ Status → `Surgery Running`. `Actual start = now()` stamped. Live timer begins on Dashboard.

### Step 7.2 — Fill the intra-op record (during or after surgery)

| Field | Example value |
|---|---|
| Incision Time | (auto-stamped at start, can adjust) |
| Closure Time | `11:15` (when skin closed) |
| Blood Loss (ml) | `50` |
| Blood Transfused (ml) | `0` |
| Operative Findings | `Inflamed gangrenous appendix, no rupture, minimal peritoneal fluid` |
| Procedure Performed | `Laparoscopic appendectomy. 3 ports. Appendix dissected, base ligated with endoloop x2, specimen retrieved in bag` |
| Operative Notes | `Standard laparoscopic technique. No complications. Estimated blood loss minimal. Specimen sent for histopathology` |
| Specimens Collected | `Appendix specimen in formalin → histopath` |
| Implants Used | (blank — no implants for appendectomy) |
| Complications | (blank) |
| Post-Op Instructions | `IV fluids 1L/8hr, IV antibiotics ceftriaxone 1g BD x 3 days, paracetamol 1g IV q6h prn, monitor vitals q15min` |
| ☑ Counts Verified | (instrument, sponge, needle counts confirmed by scrub nurse) |

Click **Save Record**.

### Step 7.3 — Complete surgery

Click **Complete Surgery** button.

> 🚨 The button is blocked unless **operative notes** AND **post-op notes** are saved. Fill Phase 9 first if not yet done.

✅ **Outcome:** Status → `Surgery Completed`. `Actual end = now()` stamped. OT room state → `Cleaning Required` (will need cleaning before next case).

---

## Phase 8 — Consumables Recording

**Who:** Scrub Nurse / Circulating Nurse (during or right after surgery)
**Where:** `/ot/consumables/45`

For each item used, click **Add Usage**:

| Sample item | Type | Qty | Rate | Total |
|---|---|---|---|---|
| Surgical Gloves (Sterile) | consumable | 4 pair | 80 | 320 |
| Surgical Mask N95 | consumable | 4 pc | 40 | 160 |
| Surgical Gown (Disposable) | consumable | 4 pc | 150 | 600 |
| Surgical Drape Set | consumable | 1 set | 400 | 400 |
| Suture - Vicryl 3-0 | consumable | 2 pc | 350 | 700 |
| Gauze Pack | consumable | 3 pack | 60 | 180 |
| IV Cannula 18G | consumable | 1 pc | 50 | 50 |
| Propofol 20ml | medicine | 1 vial | 250 | 250 |
| Atropine 0.6mg | medicine | 1 amp | 30 | 30 |

**Total consumables:** `2,690`

Tip: Use the **Select from Master** dropdown — it auto-fills name, code, unit, rate.

---

## Phase 9 — Post-Operative Notes

**Who:** Surgeon
**Where:** `/ot/post-op/45`

| Field | Example value |
|---|---|
| Procedure Summary * | `Laparoscopic appendectomy completed uneventfully. Appendix dissected, base ligated, specimen retrieved.` |
| Immediate Findings | `Gangrenous appendix, no perforation, mild peritonitis` |
| Post-Op Diagnosis | `Acute gangrenous appendicitis, status post laparoscopic appendectomy` |
| Orders | `NPO until bowel sounds return. IV fluids 1L/8hr. Vitals q15min for 2 hrs then q1h.` |
| Medications | `Ceftriaxone 1g IV BD, Metronidazole 500mg IV TDS, Paracetamol 1g IV q6h prn, Tramadol 50mg IV q8h prn` |
| Care Instructions | `Port-site dressing — check at 24hrs. Early ambulation. Spirometry 10 deep breaths/hr while awake.` |
| Follow-up Plan | `Histopath review at OPD follow-up day 7. Suture removal day 10.` |
| Disposition | `PACU` |

Click **Save Notes**. Auto-signed with current user + timestamp.

---

## Phase 10 — Recovery / PACU

**Who:** PACU Nurse + Doctor
**Where:** `/ot/pacu`

### Step 10.1 — Admit to PACU

From the schedule (now `Surgery Completed`), click **Admit to PACU**. Enter:

| Field | Example value |
|---|---|
| Bed No | `PACU-3` |

Status → `In Recovery`. Admission stamped.

### Step 10.2 — Record vitals every 15 min

Click **Add Vitals** repeatedly. Example log:

| Time | BP | Pulse | SpO₂ | Temp | Pain | Aldrete |
|---|---|---|---|---|---|---|
| 11:30 | `100/65` | `90` | `97` | `36.8` | `5` | `5` |
| 11:45 | `105/68` | `85` | `98` | `36.9` | `4` | `7` |
| 12:00 | `110/70` | `78` | `99` | `37.0` | `3` | `9` |

### Step 10.3 — Recovery Clearance (Doctor)

Once Aldrete reaches 8+ (here at 12:00 = 9), the doctor clicks **Grant Recovery Clearance**:

| Field | Example value |
|---|---|
| Consciousness Level | `Alert` |
| Clearance Notes | `Patient awake, oriented, vitals stable, pain controlled. Fit for ward transfer.` |

> 🚨 The Grant button is disabled if Aldrete < 8.

### Step 10.4 — Discharge from PACU

After clearance is granted, click **Discharge**:

| Field | Example value |
|---|---|
| Destination | `IPD` |
| Aldrete Score | `9` (final) |

✅ **Outcome:** PACU discharge stamped. PACU bed freed. PACU Nurse + OT Manager notified.

---

## Phase 11 — Transfer Back to Ward

**Who:** PACU Nurse → Porter
**Where:** `/ot/transfers`

### Step 11.1 — Initiate the return transfer

| Field | Example value |
|---|---|
| Direction | `to_ward` (or `to_icu` / `to_ccu`) |
| From | `PACU-3` |
| To | `IPD Ward — Bed 12` (original bed) |
| Porter ID | `45` |
| Nurse ID | `78` |
| Notes | `Patient stable, IV running, dressing intact` |

Click **Initiate Transfer**.

### Step 11.2 — Mark arrived at ward

When the patient reaches the ward bed, IPD nurse clicks **Mark Arrived**.

✅ **Outcome:** Schedule status → `Transferred Back`.

---

## Phase 12 — Cleaning & Sterilization

**Who:** Housekeeping / OT Nurse
**Where:** `/ot/cleaning`

After surgery completion, the OT room status is `Cleaning Required`. Housekeeping picks it up:

### Step 12.1 — Start cleaning

Find `Major OT 1` in the room tiles. Pick cleaning type:

| Field | Example value |
|---|---|
| Cleaning Type | `Routine` (or `Terminal` after infectious cases, `Emergency` for fast turn) |

Click **Start**. Status → `cleaning_in_progress`.

### Step 12.2 — Mark complete

After cleaning is done, click **Mark Complete** on the cleaning log row. Optionally fill:

| Field | Example value |
|---|---|
| Checklist | `Floor mopped, walls wiped, table sterilised, sharps disposed, biohazard collected` |
| Remarks | `Standard cleaning, all surfaces disinfected with hypochlorite 1%` |

✅ **Outcome:** Room status → `available`. Bookable again.

---

## Phase 13 — Inventory Deduction

**Who:** Inventory / Store staff
**Where:** `/ot/inventory`

The list shows consumables used that haven't been deducted from stock yet.

### Step 13.1 — Mark each deducted

For each row, click **Mark Deducted**.

System actions:
1. `OtConsumable.current_stock` decremented (e.g. Gloves: 500 → 496)
2. If linked to Pharmacy medicine, FIFO-deducts from `medicine_batches`
3. `OtStockMovement` record created (OUT, qty, balance_after)
4. If new stock < `reorder_level`, **Low Stock notification** dispatched to OT Manager

---

## Phase 14 — Billing

**Who:** Billing staff
**Where:** `/ot/billing`

### Step 14.1 — Open the completed surgery

`SS-000045` appears in the billing list (only completed/closed surgeries). Click **Bill**.

### Step 14.2 — Review estimated charges

| Line | Amount |
|---|---|
| OT Room Charge | `5,000` |
| Surgeon Fee | `8,000` |
| Anesthesia Fee | `4,000` |
| Recovery Room Charge | `1,500` |
| Consumables / Implants / Instruments | `2,690` (sum from Phase 8) |
| Emergency Surcharge | `0` (not emergency) |
| **Total** | **`21,190`** |

### Step 14.3 — Post to patient

Click **Post Charges to Patient**.

System creates `PatientCharge` rows linked to the patient's IPD admission (`case_id`, `ipd_id`, `doctor_id`, `department_id` all auto-resolved). Each consumable usage is marked `is_billed = true` and linked to its charge row.

✅ **Outcome:** Charges appear on the patient's IPD bill. Audit log records `charges_posted` with line count.

---

## Reports & Audit Trail

### To verify the whole workflow

Navigate to **OT Management → Reports & Analytics**:

| Report | What you'll see |
|---|---|
| **Surgery Log** | Filter by today's date → `John Doe — Appendectomy — SS-000045 — Surgery Completed` |
| **OT Utilization** | `Major OT 1 → 1 case, 2.0 hr scheduled` |
| **Cancellations** | (empty if no cancels) |
| **Consumables Usage** | All 9 items used in our case, total `2,690` |
| **Revenue** | Surgery count = 1, consumables billed = 2,690 |
| **Audit Trail** | Filter by entity `surgery_schedule` and id `45` → see every transition: created, status_changed (Scheduled → Pre-Op Pending → Ready for OT → Transfer Started → Patient Received → Anesthesia Started → Surgery Running → Surgery Completed → In Recovery → Transferred Back), charges_posted, with timestamps and user IDs |

---

## Quick-Reference Cheat Sheet

| Phase | Time it usually takes | Who | Main button to click |
|---|---|---|---|
| Request | 5–10 min | Doctor | **Submit Request** |
| Review | 2–5 min | Coordinator | **Accept** → **Move to Scheduling** |
| Schedule | 3–5 min | Coordinator | **Schedule Surgery** |
| Pre-Op | 30 min – 2 hrs (parallel checks) | Nurse + Anesthetist + Doctor | **Mark Complete** |
| Transfer to OT | 5–10 min | Nurse + Porter | **Initiate Transfer** → **Mark Arrived** |
| Anesthesia | 10–15 min induction | Anesthetist | **Start Anesthesia** |
| Surgery | varies (per surgery type) | Surgeon | **Start Surgery** → **Complete Surgery** |
| Consumables | concurrent with surgery | Scrub Nurse | **Add Usage** (repeat) |
| Post-Op | 5 min | Surgeon | **Save Notes** |
| PACU | 1–4 hrs typically | PACU Nurse + Doctor | **Add Vitals**, **Grant Clearance**, **Discharge** |
| Transfer back | 5–10 min | Nurse + Porter | **Initiate** → **Mark Arrived** |
| Cleaning | 15–30 min | Housekeeping | **Start** → **Mark Complete** |
| Inventory | 5 min | Store | **Mark Deducted** |
| Billing | 5 min | Billing | **Post Charges** |

**Total elapsed time for a typical appendectomy:** 4–6 hours from request submission to billing posted.

---

## Common Variations

### Emergency surgery (no time to schedule normally)

1. **OT → Emergency OT → New Emergency Case** — fill abbreviated form
2. System creates request + schedule in one transaction (both flagged emergency)
3. Pre-op: use **Emergency Override** with reason
4. Skip ahead to Phase 5 (Transfer)
5. Continue normally from Phase 6 onwards

### Surgery with implant (e.g. hernia mesh)

In Phase 1, **Required Equipment**: add `Hernia Mesh (Prolene)`.
In Phase 8, record the implant as `type = implant` (rate is usually much higher — e.g. 3,500).
Phase 14 billing will include it under "Implants / Instruments".

### Surgery requiring blood transfusion

In Phase 1, set **Blood Required = Yes**, units, group, components (PRBC / FFP / etc.), crossmatch required, blood bank instruction.
The blood bank gets notified via Pre-Op blood-arrangement check.
In Pre-Op (Phase 4), the **Blood Arranged** item must be ticked before transfer.

### High-risk patient (ASA III or IV)

In Phase 1, set ASA grade to `III` or `IV`. Add **high-risk consent** in Documents (Phase 4).
Anesthesia may use **Cleared with Risk** instead of plain **Cleared**.
Consider scheduling ICU bed in advance (mention in Special Instructions).

---

**End of walkthrough.**

This is one normal flow. The system supports many variations — refer to [OT-USER-MANUAL.md](OT-USER-MANUAL.md) for full feature reference, and the audit trail for any case to see the actual sequence of events that occurred.
