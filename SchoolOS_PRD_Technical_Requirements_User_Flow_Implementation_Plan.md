
# SchoolOS — Product Requirements Document (PRD)

**Version:** 1.0  
**Product:** SchoolOS  
**Type:** Multi-tenant SaaS for Secondary Schools, Colleges & Universities

## Product Vision

Build Africa's most intelligent **School Operating System** that manages academics, finance, communication, parent engagement, and AI-powered analytics from one platform.

## Target Institutions

- Secondary Schools
- Colleges of Education
- Polytechnics
- Universities
- Multi-campus Institutions

## User Roles

| Role | Platform |
|------|----------|
| Platform Admin | Web |
| School Super Admin | Web |
| Principal / VC | Web |
| Academic Admin | Web |
| Teacher / Lecturer | Web |
| Bursar | Web |
| Parent / Guardian | Mobile |
| Student (Phase 2) | Mobile |

---

# Core Modules

## 1. Academic Management

- Student enrollment
- Classes & arms
- Subjects
- Departments
- Faculties
- Course registration
- Grading engine
- Report cards
- Transcripts
- GPA / CGPA
- CBT examinations

## 2. Finance

- Fee invoices
- Installment plans
- Scholarships
- Payroll
- Expenses
- Digital receipts
- Audit trail

## 3. Parent Engagement

- Mobile app
- Attendance alerts
- Push notifications
- Teacher messaging
- Fee payment
- Academic progress

## 4. AI Engine

- Report comment generation
- Lesson plan generation
- Question generator
- Performance prediction
- Student Success Score

## 5. Executive Analytics

- Revenue dashboard
- Enrollment trends
- Outstanding fees
- Teacher attendance
- Academic performance
- Dropout prediction

---

# Parent Mobile App PRD

## Bottom Navigation

1. Home
2. Progress
3. Payments
4. Messages
5. More

## Home Screen

- Child profile
- Today's attendance
- Current average
- Outstanding fees
- Announcements
- Upcoming events
- Homework summary

## Progress

- Subject performance
- GPA / Average
- Class position
- Teacher comments
- AI insights

## Payments

- Pay fees
- Installment payment
- Payment history
- Download receipts

## Messaging

- Parent ↔ Teacher
- Parent ↔ Principal
- Parent ↔ Finance Office

No personal phone numbers are exposed.

---

# Technical Requirements Document (TRD)

## Architecture

Parent Mobile (Flutter)
        │
Laravel REST API (Sanctum)
        │
 PostgreSQL + Redis
        │
Notifications (FCM, SMS, WhatsApp)

## Tech Stack

| Layer | Technology |
|------|-------------|
| Backend | Laravel 12 |
| Frontend | React + Inertia |
| Mobile | Flutter |
| Database | PostgreSQL |
| Cache | Redis |
| Queue | Laravel Horizon |
| Auth | Sanctum |
| Push | Firebase FCM |
| Payments | Paystack |
| SMS | Termii |

## Multi-Tenant Strategy

Each institution gets:

- Custom subdomain
- Branding
- Logo
- Academic calendar
- Grading system
- Currency
- Institution type

Example:

schoolA.schoolos.com

university.schoolos.com

---

# Database Structure

## Core Tables

- schools
- users
- students
- parents
- parent_student
- teachers
- classes
- subjects
- departments
- faculties
- terms
- semesters
- attendance
- results
- fee_invoices
- payments
- messages
- notifications

## Parent Relationship

A parent may have multiple children.

A student may have multiple guardians.

parent_student stores:

- parent_id
- student_id
- relationship

---

# API Specification

## Authentication

POST /api/v1/parent/login

Returns Sanctum token.

## Dashboard

GET /api/v1/parent/dashboard

Returns:

- Children
- Attendance
- GPA
- Outstanding fees
- Announcements

## Progress

GET /api/v1/parent/children/{id}/progress

Returns:

- Subjects
- Scores
- Position
- Teacher comments

## Payments

POST /api/v1/payments/initialize

Starts Paystack payment.

---

# User Journey Flow

## Parent Onboarding

1. Download app
2. Enter phone number
3. Verify OTP
4. Select linked child
5. Dashboard loads

## Teacher Result Submission

1. Select class
2. Choose subject
3. Enter scores
4. AI generates comments
5. Submit
6. Principal approves

## Fee Payment

1. View invoice
2. Choose installment
3. Pay with Paystack
4. Payment verified
5. Receipt generated
6. Parent notified

---

# Implementation Plan

## Phase 1 (Weeks 1–4)

### Backend

- Parent authentication
- Guardian linkage
- Dashboard API
- Attendance API
- Notifications
- Payment integration

### Mobile

- Login
- OTP
- Home
- Attendance
- Announcements
- Fees

---

## Phase 2 (Weeks 5–7)

### Web

- GPA engine
- Transcript generator
- AI report comments
- Result analytics

### Mobile

- Progress
- Report cards
- Teacher comments

---

## Phase 3 (Weeks 8–9)

- Messaging
- Push notifications
- Broadcast announcements
- File attachments

---

## Phase 4 (Weeks 10–13)

- CBT
- AI lesson planner
- Student Success Score
- Executive analytics
- Dropout prediction

---

# Student Success Score™

A nightly AI job calculates a holistic student score using:

| Metric | Weight |
|--------|--------|
| Academics | 40% |
| Attendance | 20% |
| Assignments | 15% |
| Behaviour | 15% |
| Leadership | 10% |

This produces:

- At-risk alerts
- Parent recommendations
- Teacher interventions
- Principal analytics

---

# SaaS Pricing

| Plan | Price |
|------|------:|
| Starter | ₦25,000/month |
| Growth | ₦60,000/month |
| University | ₦150,000+/month |
| Enterprise | Custom |

Premium Add-ons:

- AI Suite
- CBT
- Payroll
- WhatsApp
- SMS Credits
- Parent App White-label

---

# 12-Month Roadmap

## Q1

- Parent app
- Fees
- Attendance
- Notifications

## Q2

- Results
- GPA
- Transcripts
- CBT

## Q3

- AI tools
- Messaging
- Analytics

## Q4

- Hostel
- Library
- Transport
- Cashless Wallet
- Marketplace Integrations

---

# Product Differentiator

SchoolOS is positioned as a **School Operating System**, not a traditional School Management System.

Key differentiators:

- AI-powered teacher tools
- Parent-first mobile experience
- Multi-tenant SaaS architecture
- Secondary + University support
- Student Success Score
- Executive analytics
- Digital transcript verification
- Cashless campus ecosystem
