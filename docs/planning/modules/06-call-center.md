# 06. Call Center / Order Confirmation

**Status:** 🟦 Planned
**Phase:** Phase 5 — Call Center + Realtime
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Agent queue ("My Queue"), call screen with masked phone + order context, disposition system (9 codes), round-robin auto-assignment with capacity caps, rule-based routing (high-value → senior agent), Pusher presence channel for agent availability, supervisor wallboard (live queue metrics), and call-task lifecycle (create on order, resolve on disposition). Call recording is v1.1 (SHOULD).

## 2. Requirements Covered

- [ ] REQ-CC-001 — "My Queue" — orders assigned to agent, sorted by age, with status chips
- [ ] REQ-CC-002 — Call action reveals masked phone; optional SIP/WebRTC integration
- [ ] REQ-CC-003 — Call screen shows order summary + fraud_score + customer history + address
- [ ] REQ-CC-004 — Disposition required to leave queue
- [ ] REQ-CC-010 — Round-robin auto-assign to online agents with configurable capacity caps
- [ ] REQ-CC-011 — Rule-based routing (high-value → senior; Bangla-pref → Bangla agents)
- [ ] REQ-CC-012 — Presence via presence-agents.call-center Pusher channel
- [ ] REQ-CC-013 — Supervisor wallboard (live queue, longest wait, per-agent occupancy, RTO today)
- [ ] REQ-CC-014 — Call recording URL on task (SHOULD — v1.1)

## 3. Design Notes

_To be filled in when module starts._

## 4. Dev Checklist

- [ ] Migrations written and reviewed
- [ ] Models + relationships
- [ ] Services / Actions (business logic)
- [ ] Controllers + FormRequests + Policies
- [ ] Routes registered + API doc snippet updated
- [ ] Jobs / Listeners / Events
- [ ] Frontend components + pages
- [ ] Feature flag gate (if any)
- [ ] Code review self-pass (PSR-12, PHPStan L6, ESLint clean)

## 5. Test Checklist

- [ ] Unit tests (≥ 80% for services here)
- [ ] Feature/HTTP tests per endpoint + policy case
- [ ] Integration tests for external calls (recorded cassettes)
- [ ] Frontend component tests
- [ ] E2E happy path (Playwright)
- [ ] Regression tests for fixed bugs
- [ ] Perf check if hot path (note target vs measured)

## 6. Acceptance Criteria

_To be filled in when module starts._

## 7. Decisions / Open Questions

- **OQ-CC-001:** "No Answer" disposition triggers auto-retry at 2h and 1d — how many total No-Answer attempts before auto-Cancel? (SRS says "N fails" without specifying N) → **blocking**
- **OQ-CC-002:** Masking format for phone — show last 4 digits only? e.g., +880 *** *** 6789 → non-blocking

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
