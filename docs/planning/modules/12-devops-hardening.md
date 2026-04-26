# 12. DevOps, Security Hardening & Launch

**Status:** 🟦 Planned
**Phase:** Phase 8 — Hardening & Launch
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Terraform IaC (VPC, ALB, ECS, RDS Multi-AZ, ElastiCache, S3, CloudFront, Route53, SES, Secrets Manager), GitHub Actions CI/CD pipeline (lint + static analysis + tests + Docker build + staging auto-deploy + prod manual-gate), blue/green or rolling deploy, OWASP ASVS L2 self-assessment, CSP headers, rate limiting hardening, k6 performance tests (PDP 500rps, place-order 100rps, search 200rps), Playwright E2E suite (happy path + top-3 failure flows), Sentry + CloudWatch alarms, DR drill runbook. SAST/DAST in CI is v1.1 (SHOULD).

## 2. Requirements Covered

- [ ] REQ-SEC-001 — OWASP ASVS L2 assessed before launch
- [ ] REQ-SEC-003 — CSP restricts scripts to self + approved vendors
- [ ] REQ-SEC-007 — Secrets in AWS Parameter Store; never in .env on disk
- [ ] NFR-PERF-001 — P95 ≤400ms API GETs (validated by k6)
- [ ] NFR-PERF-002 — P95 ≤800ms place-order (validated by k6)
- [ ] NFR-PERF-003 — LCP ≤2.5s / INP ≤200ms storefront mobile
- [ ] NFR-PERF-004 — 500 rps storefront reads, <1% errors
- [ ] NFR-PERF-005 — 100 rps place-order, <1% errors
- [ ] NFR-SCAL-001 — Stateless app tier; Redis sessions; autoscale queues
- [ ] NFR-AVAL-001 — ≥99.5% monthly uptime
- [ ] NFR-AVAL-002 — RTO 4h / RPO 30m
- [ ] NFR-AVAL-003 — Circuit breakers for all integrations
- [ ] NFR-OBS-003 — Custom CloudWatch metrics (orders/min, checkout success %, capi-match %)
- [ ] NFR-OBS-004 — Defined alert thresholds
- [ ] Section 20.1 — CI/CD: mono-repo, trunk-based, PR-gated, blue/green prod
- [ ] Section 20.2 — IaC: Terraform, per-env state in S3+DynamoDB
- [ ] Section 20.3 — E2E: Playwright happy path + top-3 failures
- [ ] Section 20.3 — Perf: k6 on PDP/place-order/search

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

- **OQ-DO-001:** ECS Fargate vs EC2-backed ECS? Fargate simpler; EC2 cheaper at scale. → non-blocking (default Fargate)
- **OQ-DO-002:** Blue/green vs rolling deploy for prod? Blue/green safer for zero-downtime; needs double capacity briefly. → non-blocking (default blue/green)
- **OQ-DO-003:** AWS account structure — single account with env namespacing, or separate prod account? → **blocking** (need from Sharifur)

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
