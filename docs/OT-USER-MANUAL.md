# OT Management — User Manual

**Version:** 1.0
**Module:** OT Management (Operation Theatre)
**Audience:** OT Coordinators, Surgeons, Anesthetists, OT Nurses, Billing & Inventory Staff, Hospital Administrators

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Roles & Permissions](#2-roles--permissions)
3. [End-to-End Surgery Workflow](#3-end-to-end-surgery-workflow)
4. [Module Reference (19 sub-menus)](#4-module-reference)
   - 4.1 [OT Dashboard](#41-ot-dashboard)
   - 4.2 [Surgery Request / OT Booking](#42-surgery-request--ot-booking)
   - 4.3 [Surgery Scheduling](#43-surgery-scheduling)
   - 4.4 [Pre-Operative Management](#44-pre-operative-management)
   - 4.5 [OT Patient Transfer](#45-ot-patient-transfer)
   - 4.6 [OT Room & Resource Management](#46-ot-room--resource-management)
   - 4.7 [OT Team Management](#47-ot-team-management)
   - 4.8 [Anesthesia Management](#48-anesthesia-management)
   - 4.9 [Surgery Execution (Intra-Op)](#49-surgery-execution-intra-op)
   - 4.10 [OT Consumables & Instrument Usage](#410-ot-consumables--instrument-usage)
   - 4.11 [Post-Operative Management](#411-post-operative-management)
   - 4.12 [Recovery / PACU Management](#412-recovery--pacu-management)
   - 4.13 [OT Billing Integration](#413-ot-billing-integration)
   - 4.14 [OT Inventory Integration](#414-ot-inventory-integration)
   - 4.15 [Cleaning & Sterilization](#415-cleaning--sterilization)
   - 4.16 [OT Documents & Consent](#416-ot-documents--consent)
   - 4.17 [Emergency OT Management](#417-emergency-ot-management)
   - 4.18 [Reports & Analytics](#418-reports--analytics)
   - 4.19 [OT Setup / Master Configuration](#419-ot-setup--master-configuration)
5. [Common Task Recipes](#5-common-task-recipes)
6. [Status Lifecycle Reference](#6-status-lifecycle-reference)
7. [Business Rules](#7-business-rules)
8. [Troubleshooting & FAQ](#8-troubleshooting--faq)

---

## 1. Introduction

The **OT Management module** is the control centre for every surgical case in the hospital. It manages the full life cycle of a surgery from the moment a doctor recommends it to the patient being transferred back to the ward after recovery.

### Module Highlights

- **One screen for live OT status** — see today's surgeries, running cases, room occupancy, delays, emergencies and PACU patients at a glance
- **Surgery booking** linked directly to IPD / OPD / ER encounters — no rekeying patient information
- **Scheduling with conflict prevention** — system blocks double booking of rooms, surgeons, anesthetists, nurses and equipment
- **Pre-op safety checklist** that must complete (or emergency-override) before a patient can move to OT
- **Live timers** for running surgeries, with expected end time and anesthesia status
- **Auto inventory deduction** on consumable / implant / medicine usage
- **Auto charge posting** to the patient's IPD / OPD / ER bill
- **Audit trail** for every status change, edit, override, approval, cancellation and reschedule

### How to access

After logging in, click **OT Management** in the left sidebar. The 19 sub-menus appear in order of the workflow.

---

## 2. Roles & Permissions

| Role | Primary responsibilities |
|---|---|
| **Surgeon / Consultant** | Recommend surgery, give pre-op clearance, sign operative notes, approve post-op transfer |
| **OT Coordinator** | Review surgery requests, assign room/team/equipment, monitor delays, prioritise emergencies |
| **Anesthetist** | Pre-anesthesia assessment, give anesthesia clearance, manage intra-op anesthesia, sign anesthesia record |
| **OT Nurse (Scrub / Circulating)** | Complete pre-op checklist, prep patient, support intra-op, record consumable usage |
| **IPD / ER Nurse** | Patient handover for transfer to OT |
| **PACU Nurse** | Monitor recovery vitals, record pain scores, request discharge clearance |
| **Billing Staff** | Verify and post charges to patient account |
| **Inventory / Store Staff** | Track and deduct consumables, implants, instruments |
| **Hospital Admin / Management** | View utilisation, cancellations, revenue, audit trail |

Each sidebar item is shown only if your role has the matching permission (e.g. `ot_schedule_access`).

---

## 3. End-to-End Surgery Workflow

```
Doctor Advice Surgery
       │
       ▼
Surgery Request  ──► (Junior approval)  ──► (Consultant approval)
       │
       ▼
Coordinator Review ──► [Send Back] / [Pending Info] / [Reject] / [Accept] / [Fast-Track]
       │
       ▼
Surgery Scheduling  (assign room + team + equipment + buffer time)
       │
       ▼
Pre-Operative Clearance  (checklist: consent / labs / radiology / fasting / anesthesia / doctor / nurse)
       │
       ▼
Transfer to OT  (from ward / ER / bed → OT room)
       │
       ▼
Anesthesia Process
       │
       ▼
Surgery Execution  (operative notes, blood loss, implants, specimens)
       │
       ▼
Consumables Usage  (every item used is logged for billing + inventory)
       │
       ▼
Post-Operative Notes
       │
       ▼
Recovery / PACU  (vitals, pain score, Aldrete, recovery clearance)
       │
       ▼
Transfer back to IPD / ICU / CCU
       │
       ▼
Billing Auto-Post + Close Schedule
```

---

## 4. Module Reference

### 4.1 OT Dashboard

**URL:** `/ot`
**Who uses it:** Everyone — first screen after login

#### What you see (top to bottom)

| Section | Purpose |
|---|---|
| **8 KPI cards** | Today Total · Running Now · Delayed · Emergency · Pending Pre-Op · Waiting Transfer · In PACU · Pending Requests. Each card is **clickable** and drills into the filtered list. |
| **OT Room Status** (full width) | Tile per room showing one of 8 states (Available / Booked / Patient Received / In Surgery / Cleaning Required / Cleaning In Progress / Ready / Maintenance). Auto-driven by schedule + cleaning events. |
| **Emergency Cases banner** | Appears only when there are active emergencies. Lists source (ER/IPD/OPD), surgery, surgeon, room, approval status. |
| **Running Surgeries** | Live duration timer (ticks every 30s), expected end time, anesthesia status. |
| **Today's Surgeries** | Time-ordered list of all today's cases. |
| **Delayed Cases** | Auto-flagged when scheduled start has passed but case hasn't moved. Shows reason: "Pre-op: Lab, Anesthesia clearance", "Patient transfer not initiated", "Previous surgery still running", etc. |
| **Pending Pre-Op** | Cards with completion-% progress bar and chip badges showing which items are missing. |
| **Post-Op Recovery** | Bed, time in recovery, latest BP/Pulse/SpO₂/Pain, Aldrete score, destination, "Ready for transfer" badge if score ≥ 8. |
| **Patients In Transit** | Anyone currently mid-transfer. |
| **Notifications** | Last 10 unread system alerts (request submitted, schedule cancelled, emergency override, low stock, etc.). |

The dashboard **auto-refreshes every 5 minutes**, but only when the page is in the foreground and you haven't scrolled or interacted in the last 30 seconds — it won't interrupt you.

---

### 4.2 Surgery Request / OT Booking

**URL:** `/ot/surgery-requests`
**Who uses it:** Doctors → coordinator review

#### Create a request

1. Click **New Surgery Request**
2. Select **Patient** (search by name / MRN / mobile)
3. Choose **Encounter Type** (IPD / OPD / ER) and optionally Encounter ID / IPD Admission ID
4. Pick **Surgery Category** → **Surgery / Procedure** (auto-fills standard duration)
5. Choose **Required OT Type** (General, Emergency, Orthopedic, Cardiac, Gynecology, Minor, Major, Endoscopy)
6. Enter **Diagnosis** (mandatory), Secondary Diagnosis, **ICD-10 Code**, Clinical Indication, Procedure Notes, ASA grade
7. Set **Priority** (Low / Normal / High / Emergency). Choosing Emergency reveals:
   - Emergency Reason (required)
   - Life-Threatening checkbox
   - Requested Immediate OT checkbox
8. Set **Preferred Date / Time / Duration** and **Flexibility** (Fixed / Flexible) with reason
9. **Blood Arrangement**: tick if required, choose units, group (from master), components (PRBC / FFP / Platelet / Whole / Cryoprecipitate), crossmatch flag, blood bank instructions
10. **Required Equipment**: click "+ Add Equipment" to add rows with quantity, mandatory/optional flag, setup instruction
11. **Approvals**: tick if junior or consultant approval is required (hierarchical)
12. **Special Instructions** for nurse / OT team
13. Click **Save Draft** to come back later, or **Submit Request** to send for review

#### Numbering

Each request gets a unique number: `OTR-YYYY-NNNNNN` (e.g. `OTR-2026-000125`).

#### Coordinator review actions

When you open a submitted request as OT Coordinator, you see these buttons:

| Button | What it does |
|---|---|
| **Start Review** | Moves Submitted → Under Review |
| **Accept** | Moves Under Review → Accepted (blocked if hierarchical approvals are pending) |
| **Send Back** | Returns to requesting doctor for correction with a reason |
| **Pending Information** | Marks waiting for external info (e.g. lab clearance) with a reason |
| **Reject** | Closes the request with a reason — terminal |
| **Fast-Track** (emergency only) | Bypasses normal flow and alerts the full OT team immediately |
| **Move to Scheduling** | Pushes Accepted / Fast-Tracked into the scheduling queue |
| **Cancel** | Withdraws the request at any non-terminal stage |

Junior / Consultant approvals are recorded with the user and timestamp. Junior must approve before Consultant if both are required.

#### Duplicate guard

If a doctor tries to create a second active request for the same patient + same surgery type, the system blocks it with a warning. Add `?duplicate_override=1` to the URL to bypass — logged in audit trail.

#### Inline actions on the list

- 👁 **View** — always visible
- ✏️ **Edit** — visible for Draft / Submitted / Pending Information / Sent Back for Correction
- 🗑️ **Delete** — visible only for Draft

Filters: search by request no / patient / MRN / mobile · status · priority · emergency only · pending info only.

---

### 4.3 Surgery Scheduling

**URL:** `/ot/schedules`
**Who uses it:** OT Coordinator

#### Create a schedule from an approved request

1. Click **+ New Schedule** (or from a request, click **Schedule**)
2. Pick the **Surgery Request** (only Accepted / Moved-to-Scheduling / Fast-Tracked appear)
3. Pick the **OT Room** (only active rooms; Emergency rooms flagged)
4. Set **Start** and **End** datetimes (end auto-calculates from surgery type's standard duration)
5. Set **Cleaning Buffer** (default 30 min) — the system blocks the room for surgery time + buffer
6. Tick **Emergency Fast-Track** to bypass conflict checks (logged)
7. Add **OT Team** row(s): Role (Primary Surgeon / Assistant / Anesthetist / Scrub Nurse / Circulating Nurse / Technician) + Staff + Specialization (for technicians: C-arm / Endoscopy / Biomedical / Anesthesia / Laparoscopy / Perfusion / Radiology)
8. Multi-select **Equipment** required
9. Click **Schedule Surgery**

The system checks:
- Room overlap (with cleaning buffer)
- Surgeon / anesthetist / nurse overlap **AND** doctor unavailability roster (leave / on-call / OPD / off-duty / meeting)
- Equipment overlap
- All staff are not assigned to another running surgery

Conflicts are listed with specific reasons:
- "Primary Surgeon (id 5): doctor is leave (May 19 09:45 – May 19 13:45)"
- "Equipment id 12 is already booked during the selected slot"
- "OT room is already booked during the selected slot"

#### Reschedule

Open a schedule → click **Reschedule** → enter new start, end, reason. System re-runs availability checks for room + all currently-active team + active equipment at the new time. Both old and new times are recorded in the audit log.

#### Cancel

Open a schedule → click **Cancel** → enter reason. The system:
- Sets status to `Cancelled`
- Marks all team members `released_at = now()` with the cancellation reason
- Marks all equipment `released_at = now()` so the slots immediately free up for new bookings

#### Calendar view

`/ot/schedules-calendar` shows a day-grid of all rooms with each surgery as a coloured block, sized by duration. Click the block to open the schedule.

---

### 4.4 Pre-Operative Management

**URL:** `/ot/pre-op`
**Who uses it:** Nurses, Doctors, Anesthetists

After a schedule is created, a **pre-op checklist** is automatically created. The list view shows all upcoming schedules with their completion progress bar and chips indicating missing items.

#### Required items (must complete before transfer)

1. Consent Obtained
2. Lab Tests Completed
3. Radiology Completed
4. NPO / Fasting Confirmed
5. Blood Arranged
6. Allergies Reviewed
7. Vitals Recorded
8. Anesthesia Clearance
9. Doctor Clearance
10. Nurse Confirmation

#### Optional items

- Surgical Site Marked
- Patient ID Band Verified
- Vitals snapshot (BP, pulse, temp, RR, SpO₂)
- Notes

#### Actions

- **Save Progress** — saves checkboxes without finalising. Schedule status moves to `Pre-Op Pending` when first item is ticked.
- **Mark Complete (Ready for OT)** — runs `isReady()` check (all 10 required items ticked OR emergency override applied). If passed, schedule status moves to `Ready for OT` and OT Nurse + Surgeon + Anesthetist are notified.
- **Emergency Override** — bypasses missing items with a reason. Records `emergency_override = true`, `override_approved_by`, `emergency_override_reason`. Sends emergency alert.

---

### 4.5 OT Patient Transfer

**URL:** `/ot/transfers`
**Who uses it:** IPD/ER Nurse, OT Nurse, Porter

#### Initiate transfer

From the dashboard or schedule, click **Transfer to OT** when the patient is `Ready for OT`. Fill in:
- Direction: `to_ot`, `to_pacu`, `to_ward`, `to_icu`, `to_ccu`
- From location (free text)
- To location (auto from room)
- Porter ID, Nurse ID
- Notes

**Pre-op gate:** the system blocks transfer to OT until the pre-op checklist is complete (or emergency override applied). The error message tells you exactly which items are missing.

#### Mark arrived

When the patient physically arrives at the destination, click **Mark Arrived** on the transfer record. This:
- Stamps `arrived_at = now()`
- For `to_ot` → schedule status becomes `Patient Received in OT`
- For `to_ward / to_icu / to_ccu` → schedule status becomes `Transferred Back`

---

### 4.6 OT Room & Resource Management

**URL:** `/ot/rooms`
**Who uses it:** Hospital Admin, OT Coordinator

CRUD on OT rooms with:
- Code (unique, e.g. `OT-01`)
- Name
- Type (Major / Minor / Day Care / Emergency / Endoscopy / Cath Lab)
- **Floor** (linked to master `/floors`)
- Block
- Status (managed automatically by schedule lifecycle)
- Emergency OT flag
- Active flag
- Description

The **Room Status** view (`/ot/room-status`) shows a card per room with today's case count and any running case.

---

### 4.7 OT Team Management

**URL:** `/ot/teams`
**Who uses it:** OT Coordinator

Per-schedule team roster:
- Add team members with role + staff + specialization
- Remove members (system records `released_at` and reason)
- Conflict check runs every time you add a member

The schedule show page already includes a Team Members panel; this dedicated page is for managing teams across multiple schedules.

---

### 4.8 Anesthesia Management

**URL:** `/ot/anesthesia`
**Who uses it:** Anesthetist

For a schedule, record:
- **Anesthesia Type** (master: General / Spinal / Epidural / Local / Regional Block / MAC Sedation)
- Anesthetist ID
- Induction Time, Recovery Time
- Pre-Anesthesia Assessment
- Drugs Used
- Airway Management
- Intra-Op Vitals (JSON time series)
- Complications
- Post-Anesthesia Notes
- ASA Grade

**Start Anesthesia** button transitions the schedule status from `Patient Received in OT` → `Anesthesia Started`.

---

### 4.9 Surgery Execution (Intra-Op)

**URL:** `/ot/intra-op`
**Who uses it:** Surgeon, Scrub Nurse

For each schedule, fill the operative record:
- Incision Time, Closure Time
- Blood Loss (ml), Blood Transfused (ml)
- Operative Findings
- Procedure Performed
- **Operative Notes** (required to complete surgery)
- Specimens Collected
- Implants Used
- Complications
- Post-Op Instructions
- Counts Verified (instrument / sponge / needle)

#### Status transitions

- **Start Surgery** — Anesthesia Started → Surgery Running. Stamps `actual_start = now()` and creates the operative record with `incision_time = now()`.
- **Complete Surgery** — Surgery Running → Surgery Completed. **Requires** operative notes AND post-op notes to be saved. Stamps `actual_end = now()` and `signed_by + signed_at` on the operative record.

---

### 4.10 OT Consumables & Instrument Usage

**URL:** `/ot/consumables`
**Who uses it:** Scrub Nurse, OT Nurse

Per schedule, log every item used:
- Item Name (or pick from master to auto-fill)
- Item Code
- Type: consumable / implant / instrument / medicine
- Quantity, Unit, Rate (auto-fills total amount)
- Notes

You can also pick from the master in a single click — the form auto-fills name, code, rate, unit and links to the master.

Items cannot be deleted once they've been billed or inventory-deducted.

---

### 4.11 Post-Operative Management

**URL:** `/ot/post-op`
**Who uses it:** Surgeon, Anesthetist

For each schedule, save post-op notes:
- **Procedure Summary** (required)
- Immediate Findings
- Post-Op Diagnosis
- Orders
- Medications
- Care Instructions
- Follow-up Plan
- Disposition (PACU / Ward / ICU / CCU / Home)

The note is auto-signed with the current user and timestamp.

---

### 4.12 Recovery / PACU Management

**URL:** `/ot/pacu`
**Who uses it:** PACU Nurse, Doctor

#### Admit a patient to PACU

From the schedule, click **Admit to PACU** (requires `Surgery Completed` status). Enter:
- Bed number

Status moves to `In Recovery`.

#### Record vitals

For each PACU patient, click **Add Vitals** repeatedly throughout the stay:
- BP, Pulse, SpO₂, Temp
- Pain Score (0–10)
- Aldrete Score (0–10)
- Notes

All entries are appended to a JSON vitals log with timestamps and the user who recorded them.

#### Recovery clearance (mandatory before discharge)

A doctor must grant **Recovery Clearance** before the patient can leave PACU. The button:
- Requires `aldrete_score ≥ 8` (button is disabled below 8)
- Captures consciousness level (Alert / Drowsy / Responding to voice / Responding to pain / Unresponsive)
- Optional clearance notes
- Records `cleared_by` and `cleared_at`

#### Discharge

Once clearance is granted:
- Pick destination (IPD / ICU / CCU / Ward / Home)
- Set final Aldrete score
- Click **Discharge**

Without clearance the Discharge button is disabled — a red "Discharge locked" banner explains why.

---

### 4.13 OT Billing Integration

**URL:** `/ot/billing`
**Who uses it:** Billing staff

For completed surgeries, the billing show page lists:
- OT Room Charge
- Surgeon Fee
- Anesthesia Fee
- Recovery Room Charge
- Consumables / Implants / Instruments (sum)
- Emergency Surcharge (15% if `emergency_fast_track`)

Click **Post Charges to Patient** to push every chargeable line as a `PatientCharge` row linked to the patient's IPD / OPD / ER encounter. Consumable usages get flagged `is_billed = true` and linked to their `patient_charge_id`.

The encounter context (case_id / ipd_id / opd_id / er_register_id, doctor_id, department_id) is auto-resolved from the surgery request — no manual entry.

---

### 4.14 OT Inventory Integration

**URL:** `/ot/inventory`
**Who uses it:** Inventory / Store staff

Lists consumable usages that have not yet been inventory-deducted. Click **Mark Deducted** to:
1. Decrement the OT consumable's `current_stock`
2. If the consumable is linked to a Pharmacy medicine, FIFO-deduct from `medicine_batches`
3. Record a stock movement (`OtStockMovement`)
4. If new stock falls below `reorder_level`, dispatch a **Low Stock** notification to the OT Manager

---

### 4.15 Cleaning & Sterilization

**URL:** `/ot/cleaning`
**Who uses it:** Housekeeping, OT Nurse

After a surgery completes, the room status flips to `cleaning_required`. Pick a cleaning type (Routine / Terminal / Emergency) and click **Start** — the room becomes `cleaning_in_progress`.

When done, click **Mark Complete** on the cleaning log. The room becomes `available` and is bookable again.

The Cleaning Log table tracks who performed and verified, start/end times, and any remarks.

---

### 4.16 OT Documents & Consent

**URL:** `/ot/documents`
**Who uses it:** Nurse, Doctor, Patient (via attendant)

Upload supporting documents linked to a surgery request or schedule:
- Surgery Request ID / Schedule ID
- Document type (consent / pre_op / intra_op / post_op / discharge / other)
- Title
- File (PDF, image, max 10 MB)
- Notes

Actions per document:
- **Download** — get the file
- **Sign** — marks `is_signed = true` and stamps `signed_at`
- **Delete** — removes from disk + DB

---

### 4.17 Emergency OT Management

**URL:** `/ot/emergency`
**Who uses it:** ER doctor, OT coordinator on-call

For life-threatening cases that bypass normal scheduling:

1. Click **New Emergency Case**
2. Select patient (search), encounter type (ER / IPD / OPD)
3. Pick surgery type and surgeon
4. Pick an **Emergency OT** room (only flagged rooms shown)
5. Set start / end times (default: now + 2h)
6. Enter clinical indication and fast-track reason
7. Submit

The system creates the surgery request and schedule in a single transaction, both flagged `emergency_fast_track = true`. Audit log records both events. Notifications fire to surgeon, anesthetist, OT nurse, ward.

To approve a queued emergency: open the case and click **Approve** — stamps `approved_by`, `approved_at`.

---

### 4.18 Reports & Analytics

**URL:** `/ot/reports`
**Who uses it:** Hospital Admin, Management

Six reports, each with date-range filter:

| Report | What it shows |
|---|---|
| **Surgery Log** | All surgeries in date range with patient, surgeon, room, status |
| **OT Utilization** | Per-room case count + total scheduled hours |
| **Cancellations** | All cancelled schedules with reason |
| **Consumables Usage** | Item-level usage with totals |
| **Revenue Summary** | Consumables billed total + surgery count |
| **Audit Trail** | Every state change with entity / action / from→to / user / IP / reason — filterable by entity type, action, date range |

---

### 4.19 OT Setup / Master Configuration

**URL:** `/ot/setup`
**Who uses it:** Hospital Admin

Landing page with 6 tiles, each leading to a CRUD master:

1. **OT Rooms** — code, name, type, floor (from master), block, emergency flag
2. **Equipment** — code, name, category, default room, serial, status, service dates
3. **Surgery Categories** — Major / Minor / Day Care / Emergency / Endoscopy / etc.
4. **Anesthesia Types** — General, Spinal, Epidural, Local, Regional Block, MAC, etc.
5. **Surgery Types** — name, code, category, standard duration, standard / surgeon / anesthesia / room / recovery charges
6. **Consumables / Implants / Instruments** — name, code, type, unit, rate, current stock, reorder level, linked medicine

Sample data is seeded via `php artisan db:seed --class=OtSampleDataSeeder` (5 rooms, 11 equipment, 5 categories, 6 anesthesia types, 8 surgery types, 15 consumables).

---

## 5. Common Task Recipes

### 5.1 Schedule today's elective surgery

```
Doctor: open IPD patient profile → click Request Surgery → fill in details → Submit
Coordinator: open OT → Surgery Requests → click new entry → Start Review → Accept → Move to Scheduling
            → from Surgery Request show page → click Schedule
            → pick room + time + team → Schedule Surgery
```

### 5.2 Run an emergency surgery in under 10 minutes

```
ER doctor: OT → Emergency OT → New Emergency Case
         → patient, surgery, room, surgeon, reason → Submit
System: alerts entire OT team
Coordinator: Approve the case
PACU/IPD nurse: Pre-op checklist → Emergency Override (with reason)
Porter: Transfer to OT → Mark Arrived
Anesthetist: Start Anesthesia
Surgeon: Start Surgery → fill operative notes → Save → Complete Surgery
PACU: Admit → record vitals → Recovery Clearance → Discharge
```

### 5.3 Reschedule a surgery

```
OT → Schedules → open the case → Reschedule
Enter new start, end, reason → Save
System re-runs availability for room + all team + all equipment
If conflicts: change times or release conflicting resources first
```

### 5.4 Post charges after surgery

```
OT → Billing → open completed schedule
Verify estimated charges (OT room, surgeon, anesthesia, recovery, consumables, emergency surcharge)
Click Post Charges to Patient
System creates PatientCharge rows; consumables marked is_billed = true
```

### 5.5 Find why a surgery is delayed

```
OT Dashboard → look at "Delayed Cases" panel
Each row shows the delay reason ("Pre-op: Lab, Anesthesia clearance" / "Previous surgery still running" / etc.)
Click the patient name to drill in and resolve the blocker
```

---

## 6. Status Lifecycle Reference

### Surgery Request

```
Draft → Submitted → Under Review → Pending Information ⇄ Submitted
                                 → Sent Back for Correction ⇄ Submitted
                                 → Accepted → Moved to Scheduling → Scheduled
                                 → Rejected (terminal)
                                 → Emergency Fast-Tracked → Moved to Scheduling
                  → Cancelled (any non-terminal state)
```

### Surgery Schedule

```
Scheduled → Pre-Op Pending → Ready for OT → Transfer Started → Patient Received in OT
        → Anesthesia Started → Surgery Running → Surgery Completed
        → In Recovery → Transferred Back → Closed
        → Cancelled / Rescheduled (any non-terminal state)
```

### OT Room

```
Available → Booked → Patient Received → In Surgery → Cleaning Required
        → Cleaning In Progress → Available
        → Under Maintenance (manual transition by admin)
        → Ready (after final inspection)
```

### Pre-Op Checklist

```
(no checklist) → In Progress (at least 1 item checked) → Complete / Emergency Override Approved
```

### Transfer

```
Initiated → Arrived
```

### PACU

```
In Recovery → (vitals logged) → Recovery Clearance Granted → Discharged
```

---

## 7. Business Rules

These are enforced by the system — you cannot bypass them in normal flow.

1. **Pre-op gate** — A patient cannot transfer to OT until the mandatory pre-op checklist is complete (or emergency override applied with reason).
2. **Cleaning gate** — An OT room cannot be marked `available` until cleaning is recorded as complete.
3. **Emergency priority** — Emergency cases sort to the top of every dashboard list.
4. **Live duration** — Running surgeries display a live timer (updates every 30 seconds).
5. **Delay reason** — Delayed surgeries must show the specific blocker.
6. **Surgery completion** — Surgery cannot be marked completed without operative notes AND post-op notes being saved.
7. **Recovery transfer** — A patient cannot leave PACU without doctor recovery clearance (Aldrete ≥ 8 required to grant clearance).
8. **Double-booking** — Same OT room, surgeon, anesthetist, nurse, or equipment cannot be booked for overlapping slots (unless emergency fast-track is ticked).
9. **Doctor roster** — Scheduling checks the doctor unavailability table (leave / on-call / OPD / off-duty) and blocks assignment.
10. **Approval hierarchy** — If junior approval is required, it must be granted before consultant approval. Coordinator's Accept button is disabled until all required approvals are recorded.
11. **Audit log** — Every status change, edit, override, approval, cancellation, reschedule, and resource release is logged.

---

## 8. Troubleshooting & FAQ

### "The PUT method is not supported for route X"

You probably clicked a button that was inside an outer form using `@method('PUT')`. Refresh the page — the latest version of the OT module separates Save Progress (PUT) and Mark Complete (POST) into distinct forms.

### "Pre-op checklist won't mark as complete"

Check that all 10 required items are ticked. If a item is genuinely not applicable (e.g. blood not required for this surgery), use **Emergency Override** with a clear reason. Override is audited.

### "Cannot schedule — system says staff unavailable"

The system checks two things:
1. **Existing OT bookings** for the same staff at the same time
2. **Doctor unavailability** (leave / on-call / OPD / off-duty / meeting)

The error message tells you exactly which one. To resolve:
- Reassign to another staff member
- Or release the existing booking
- Or remove the unavailability window
- Or use **Emergency Fast-Track** to bypass

### "Discharge from PACU is locked"

The patient needs **Recovery Clearance** first. Open the PACU record:
- If Aldrete < 8, keep monitoring and recording vitals
- Once Aldrete reaches 8, a doctor can grant clearance
- After clearance, the Discharge button becomes enabled

### "Charges aren't appearing on the patient's IPD bill"

Verify:
1. The surgery request has the correct **Encounter Type** and **Encounter ID / IPD Admission ID**
2. You clicked **Post Charges to Patient** on the Billing show page
3. The patient's IPD admission is active

If the encounter type is wrong, edit the surgery request before billing.

### "Notification didn't fire to the surgeon"

The notification system writes to `ot_notifications` and shows them on the OT Dashboard's Notifications panel. If you're not seeing them:
1. Check the role assignment (surgeons need the role name "Surgeon" or similar in your permission seeder)
2. Check `/ot/dashboard` Notifications panel — unread notifications appear there

### "How do I close a schedule after surgery + recovery + ward transfer?"

After PACU discharge → ward arrival is marked → status becomes `Transferred Back`. Open the schedule and complete the workflow (a Close action is on the schedule show page).

### "Where do I add a new technician specialisation?"

The specialisations are predefined constants (C-arm, Endoscopy, Biomedical, Anesthesia, Laparoscopy, Perfusion, Radiology, Other). To add more, contact your system administrator to update `App\Models\Ot\OtSurgeryTeam::SPECIALIZATIONS`.

### "I see 'Staff #5' instead of doctor name in the team list"

This is a known limitation — the team member's polymorphic relation (Doctor vs User vs Staff) is not yet resolved in the UI. Hover over the ID to confirm, and check the audit log if needed.

---

## Appendix: Permission List

The module uses 44 permissions seeded by `PermissionSeeder`:

```
ot_dashboard_access
ot_surgery_request_access, ot_surgery_request_create, ot_surgery_request_edit,
ot_surgery_request_review, ot_surgery_request_delete
ot_schedule_access, ot_schedule_create, ot_schedule_edit, ot_schedule_approve,
ot_schedule_cancel, ot_schedule_reschedule
ot_pre_op_access, ot_pre_op_edit, ot_pre_op_complete, ot_pre_op_override
ot_transfer_access, ot_transfer_create
ot_room_access, ot_room_manage
ot_team_access, ot_team_manage
ot_anesthesia_access, ot_anesthesia_edit
ot_intra_op_access, ot_intra_op_edit
ot_consumables_access, ot_consumables_edit
ot_post_op_access, ot_post_op_edit
ot_pacu_access, ot_pacu_edit
ot_billing_access, ot_billing_post
ot_inventory_access
ot_cleaning_access, ot_cleaning_manage
ot_documents_access, ot_documents_upload
ot_emergency_access, ot_emergency_create, ot_emergency_approve
ot_reports_access, ot_audit_access
ot_setup_access, ot_setup_manage
```

Assign these to roles via `/admin/roles`. Users with the **Super Admin** role bypass all permission checks.

---

**End of manual.** For technical / development docs, see source code and inline comments. For bug reports or feature requests, contact your IT administrator.
