# 🤖 Prompt untuk Claude.ai - SPPD UI/UX Redesign Decision Making

> Copy-paste prompt ini ke chat.claude.ai untuk mendapatkan rekomendasi strategis

---

## **PROMPT #1: Strategic Assessment**

```
Saya sedang mempertimbangkan UI/UX redesign untuk aplikasi internal SPPD (Sistem Perjalanan Dinas) di universitas. Berikut konteksnya:

## APLIKASI CONTEXT:
- **Name:** eSPPD (e-Sistem Perjalanan Dinas)
- **Institution:** UIN SAIZU Purwokerto
- **Stack:** Laravel 11 + Livewire (Volt) + Blade + Tailwind CSS
- **Database:** PostgreSQL
- **Current Users:** 474 active users
- **User Roles:** 8 roles with 5 authorization levels (Level 1-99)
- **Main Workflow:** Draft → Submit → Approval → Realisasi (multi-stage)

## CURRENT STATE (Production):
✅ Application fully operational
✅ All migrations applied (28 total)
✅ RBAC system working (10 authorization gates)
✅ HTTP accessible at 192.168.1.27:8083
✅ Zero critical logic errors
❌ UI/UX dated (dense tables, overwhelming menus)

## REDESIGN PROPOSAL:
**Phase 1:** Role-based dashboard redesign
- Remove irrelevant menu items per role
- Replace dense tables with card-based layout
- Add quick stats/overview
- Progressive disclosure for details
- Target: 2-3 weeks implementation

## MY QUESTIONS:

1. **Feasibility:** Dengan stack Laravel + Livewire + Tailwind, seberapa sustainable approach ini? Ada gotchas yang perlu diawasi?

2. **Rollout Strategy:** Apa yang paling safe? 
   - Option A: Beta with 10% users (2 weeks) → Full rollout
   - Option B: Big bang (all users same time)
   - Option C: Parallel run (old + new available)
   - What's your recommendation?

3. **Risk Mitigation:** 
   - Apa top 3 risks untuk rollout? 
   - Bagaimana mitigasi masing-masing?
   - Apa success metrics untuk track?

4. **Timeline:** Realistic timeline dengan:
   - Design finalization: ?
   - Development: ?
   - Testing/QA: ?
   - Beta phase: ?
   - Full rollout: ?
   - Buffer untuk issues: ?

5. **Resource Planning:**
   - Berapa developers minimal yang butuh?
   - QA effort estimate?
   - Training/support effort?

6. **User Communication:**
   - Bagaimana menyiapkan 474 users untuk perubahan ini?
   - What messaging strategy works best untuk internal enterprise app?
   - Kapan announce? Before/after launch?

7. **Fallback Plan:**
   - Jika redesign bikin users confused, rollback plan apa yang viable?
   - Berapa lama maintenance parallel systems?

8. **Success Definition:**
   - Specific metrics untuk dukung redesign "sukses"?
   - Expected improvement dalam user satisfaction/efficiency?
   - Breakeven point untuk ROI?

Berikan analisis yang practical, bukan hanya conceptual. I need actionable recommendations.
```

---

## **PROMPT #2: Implementation Planning**

```
Lanjutan dari keputusan untuk proceed dengan redesign dashboard SPPD. Sekarang saya butuh implementation planning:

## CURRENT SETUP:
- Framework: Laravel 11 + Livewire (Volt components)
- Styling: Tailwind CSS (v3.x)
- Frontend: Blade templates + Alpine.js
- Database: PostgreSQL (474 users established)
- Users: 8 roles, hierarchical permissions already defined

## REDESIGN SCOPE:
**Phase 1: Dashboard Role-Based Redesign**

Current dashboard shows:
- Dense sidebar dengan 7-8 menu items (sama untuk semua roles)
- Single table view dengan ratusan SPPD entries
- No overview/stats
- Mobile unfriendly

Target dashboard (Level 1 - Dosen):
- Sidebar: 3 relevant menu items saja
- Overview: Cards dengan quick stats (My Draft, Pending Approval, Approved)
- Main content: My SPPDs grouped by status (Draft, Submitted, Approved)
- CTA: "Create New SPPD" button prominent
- Mobile: Full responsive

Target dashboard (Level 3 - Manager):
- Sidebar: 6 items (Team view, Approval queue, Budget, Analytics, Users, Delegation)
- Overview: Stats untuk team + urgent approvals
- Main: Approval queue (sorted by overdue)
- Actions: Inline approve/reject + delegation

## IMPLEMENTATION QUESTIONS:

1. **Component Architecture:**
   - Apa struktur best untuk Livewire Volt components dalam context ini?
   - Bagaimana handle state management untuk multiple role dashboards?
   - Caching strategy untuk performance (474 users)?

2. **Database Query Optimization:**
   - Current queries untuk dashboard load semua SPPD?
   - Optimization untuk role-based filtering:
     ```
     Level 1: Show only user's own SPPD
     Level 2: Show own + team SPPDs
     Level 3+: Can see all + filters
     ```
   - N+1 query prevention strategies?
   - Indexes needed?

3. **Feature Breakdown:**
   Task 1: Design tokens (colors, spacing, typography) → Effort?
   Task 2: Refactor Button/Input components → Effort?
   Task 3: Build new Dashboard component → Effort?
   Task 4: Implement role-based filtering → Effort?
   Task 5: Build Stats cards → Effort?
   Task 6: Implement SPPD card component → Effort?
   
   Which should be parallel? Which sequential?

4. **Testing Strategy:**
   - Unit tests untuk new components? (effort vs value?)
   - Feature tests untuk approval workflow tidak rusak?
   - E2E tests untuk critical paths?
   - Load testing dengan 474 users?

5. **Deployment & Rollback:**
   - How to A/B test new dashboard dengan existing 10% users?
   - Database migrations needed atau purely frontend?
   - Rollback strategy jika issue detected?
   - Monitoring metrics to watch?

6. **Code Review Checklist:**
   Apa yang harus dicheck ketika review PR untuk redesign ini?
   - Performance considerations?
   - Accessibility (WCAG) compliance?
   - Mobile responsiveness?
   - Cross-browser testing?

7. **Documentation:**
   - Component library documentation needed?
   - Design system docs (color, spacing, typography)?
   - User migration guide?
   - Developer guide untuk future modifications?

Saya perlu practical step-by-step implementation plan yang bisa saya assign ke developers.
```

---

## **PROMPT #3: Risk & Contingency Planning**

```
SPPD dashboard redesign sedang di-plan untuk Phase 1 implementation. Saya butuh deep-dive ke risk analysis:

## CONTEXT:
- 474 active internal users (university staff + lecturers)
- Critical workflow: Approval process untuk business travel
- Current system: Zero downtime requirement (ongoing usage)
- Timeline: 3-4 weeks development + 2 weeks beta + 1 week rollout

## RISKS IDENTIFIED:

1. **User Adoption Risk** (HIGH)
   - "Biasanya saya begini, jadi bingung kalau ganti"
   - What's the historical adoption rate untuk UI changes di organisasi?
   - Bagaimana mitigate change fatigue?

2. **Performance Regression** (MEDIUM)
   - New dashboard pake card-based layout + multiple API calls
   - Risk: Dashboard lebih slow dari sebelumnya
   - Mitigation: Specific performance thresholds? Monitoring plan?

3. **Approval Workflow Disruption** (HIGH)
   - Redesign bukan bikin fitur baru, tapi cara display berubah
   - Risk: Manager confusion → approval delays
   - Bagaimana ensure manager bisa approve dengan mudah di UI baru?

4. **Mobile Experience Issues** (MEDIUM)
   - Assumed users primarily desktop, tapi bagaimana mobile?
   - Card-based layout harus tested di real devices?
   - Fallback untuk older browsers?

5. **Data Consistency** (MEDIUM)
   - Database data struktur tetap sama, UI display berubah
   - Risk: Bug dalam filtering logic → show wrong data
   - Testing strategy untuk data integrity?

6. **Browser Compatibility** (LOW)
   - Tailwind CSS modern features support di IE? (probably not used, but check)
   - Livewire Volt maturity level - any known issues?

7. **Rollback Complexity** (MEDIUM)
   - Jika rollback needed, how long downtime?
   - Data loss risk?
   - User confusion if rollback?

## QUESTIONS:

1. **Top 3 Highest Impact Risks:**
   Dari list di atas, mana 3 yang most critical to mitigate? Kenapa?

2. **Contingency For Each:**
   For top 3 risks, apa contingency plan yang realistis?
   Example:
   - If approval workflow confused → quick hotline + video tutorial?
   - If performance degradation → rollback or quick optimization?
   - If adoption slow → extended beta atau incentivize early adopters?

3. **Early Warning Indicators:**
   Selama beta phase, apa signs yang indicate something going wrong?
   - Specific metrics?
   - User feedback patterns?
   - Technical indicators?

4. **Exit Strategy:**
   Apa criteria untuk:
   - Continue dari beta ke full rollout?
   - Pause dan fix issues?
   - Full rollback?

5. **User Support During Transition:**
   - Berapa support team needed?
   - Training materials format? (video, docs, live session?)
   - Support duration post-launch? (1 week, 2 weeks, 1 month?)

6. **Monitoring & Metrics:**
   What specific KPIs track post-launch?
   - Technical: Page load time, error rate, uptime?
   - User: Task completion time, support tickets, satisfaction?
   - Business: Approval speed, SPPD processing time?
   - Thresholds untuk each metric?

7. **Communication Plan:**
   Timeline & messaging untuk:
   - Pre-announcement (when? what message?)
   - Beta announcement (to 10% users, what to expect?)
   - Full launch announcement (confidence building?)
   - Post-launch support (how to ask for help?)

8. **Decision Criteria:**
   If beta phase shows 30% error rate atau 50% adoption resistance, apa keputusan next step?
   - Predefined decision tree?

Berikan risk assessment yang actionable, bukan just list of risks.
```

---

## **PROMPT #4: ROI & Justification**

```
SPPD redesign proposal kebutuhan justification untuk approval dari management. Help me build the business case:

## BUSINESS CONTEXT:
- Organization: UIN SAIZU Purwokerto (Islamic university)
- System: SPPD (Business travel management)
- Users: 474 (lecturers + administrative staff)
- Budget constraints: Government-funded, tight budget
- Decision makers: IT Director, Academic VP

## CURRENT PAIN POINTS (Anecdotal):
- Support team receives ~50+ tickets/week tentang UI confusion
- Approval process avg 2-3 days (wanted: 1 day)
- New users require 2-3 hour training untuk navigate
- Mobile access reported but not optimized
- Manager complaints: "Hard to find pending approvals"

## PROPOSED REDESIGN:
- Investment: ~$5,000-6,500 (dev + test + training)
- Timeline: 3-4 weeks dev + 2 weeks beta + 1 week rollout (6 weeks total)
- Scope: Dashboard redesign + role-based filtering (Phase 1 only)
- Expected benefit: Faster user onboarding, reduced support tickets, faster approvals

## QUESTIONS FOR CLAUDE:

1. **ROI Calculation:**
   Current model saya estimate:
   - Reduced support: 10 tickets/week × 52 weeks × $50/ticket = $26,000/year
   - Efficiency gains: 10% faster = $8,000/year
   - Total: ~$39,000 benefit vs $6,500 investment = 6x ROI
   
   Ini realistic? Apa missing components dalam calculation ini?
   Apa assumptions yang riskier?

2. **Intangible Benefits:**
   Beyond financial ROI, apa intangible benefits?
   - Staff satisfaction/morale impact?
   - Organizational agility (easier to iterate)?
   - Risk reduction (fewer errors)?
   - How to communicate intangibles ke management?

3. **Competitor/Best Practice:**
   Bagaimana other universities handle SPPD systems?
   Apa benchmark bisa digunakan untuk justify modern UI?

4. **Phased Approach Justification:**
   Kenapa Phase 1 (dashboard) dulu, bukan langsung full redesign?
   - Cost saving vs benefit: worth it?
   - Risk reduction: meaningful difference?
   - How to explain phased approach ke management?

5. **Opportunity Cost:**
   - Jika tidak redesign, apa risk organisasi?
   - User turnover due to frustration? (unlikely but mention?)
   - Regulatory/audit issues if approval slow? (SPPD adalah compliance need?)
   - Innovation perception (modern vs dated)?

6. **Implementation Risk Impact:**
   Jika redesign gagal (rollback needed):
   - Sunk cost: $6,500 gone (how to frame this?)
   - Repeat investment: Buat ulang dari awal?
   - How to minimize "failure cost" perception?

7. **Success Metrics untuk Report:**
   Apa metrics bisa ditrack & report post-launch untuk show ROI?
   - Support tickets: "Decreased from 50/week to 20/week"
   - Task time: "Average approval time reduced from 2.5 days to 1.2 days"
   - User satisfaction: "80% positive feedback in survey"
   - Mobile adoption: "30% increase in mobile access"
   
   Ini trackable metrics or too ambitious?

8. **Presentation Strategy:**
   Untuk present ke decision makers (IT Director, VP Academic):
   - What's the hook/opening statement?
   - Data to emphasize?
   - Risks to downplay/acknowledge honestly?
   - Call to action (approval process)?

Berikan business case yang konkret, pakai numbers konkret, dan defensible assumptions.
```

---

## **PROMPT #5: Quick Consultation**

Jika mau yang quick & concise:

```
I'm planning UI/UX redesign for SPPD (internal travel system) di universitas. 
474 users, Laravel + Livewire + Tailwind stack.

Scope: Dashboard redesign (role-based, card layout, remove clutter)
Timeline: 3-4 weeks dev
Budget: ~$5-6K
Risk: User adoption, performance, approval workflow disruption

Secara garis besar, ini ide bagus atau risky? Apa recommendation paling penting yang perlu aku perhatikan?
```

---

## **TIPS MENGGUNAKAN PROMPTS INI:**

1. **Gunakan satu per satu** (jangan semuanya di satu chat)
   - Setiap prompt membangun fokus spesifik
   - Claude bisa lebih deep dive per topik

2. **Customize dengan detail lokal**
   - Replace "UIN SAIZU" dengan institusi real
   - Ubah numbers (474 users) dengan actual data
   - Adjust budget/timeline ke kondisi real

3. **Iterasi** - jika Claude output tidak sesuai:
   - "Yang tadi bagus tapi aku butuh lebih fokus pada X"
   - "Bisa detailkan bagian Y?"
   - "Gimana kalau scenario berbeda: ...?"

4. **Export hasil** untuk:
   - Share ke team
   - Present ke management
   - Document decision making process

5. **Combine output** dari multiple prompts:
   - Prompt #1 (Strategic) → Keputusan approval
   - Prompt #2 (Implementation) → Technical planning
   - Prompt #3 (Risk) → Mitigation strategies
   - Prompt #4 (ROI) → Management presentation
   - Result: Complete decision document

---

## **STRUKTUR IDEAL UNTUK DECISION:**

```
1. Strategic Assessment (Prompt #1)
   ↓ (Is this worth doing?)
   
2. ROI & Justification (Prompt #4)
   ↓ (Can we afford it?)
   
3. Risk & Contingency (Prompt #3)
   ↓ (Can we handle the risks?)
   
4. Implementation Planning (Prompt #2)
   ↓ (How do we actually do it?)
   
5. Final Go/No-Go Decision
   ↓
   
6. Communicate & Execute
```

---

**Gunakan prompts ini untuk get expert perspective, tapi keputusan final tetap di tangan tim kamu!** 🚀
