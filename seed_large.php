<?php
// Seed Large Volume of Mock Data for CAVA LMS (10+ records per module)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

try {
    $pdo = DB::getConnection();
    echo "Connected to database successfully.<br>";
    
    // Get category IDs mapping
    $stmt = $pdo->query("SELECT id, slug FROM categories");
    $catMap = [];
    while ($row = $stmt->fetch()) {
        $catMap[$row['slug']] = $row['id'];
    }
    
    // Fallbacks if categories don't exist
    $ieltsCatId = $catMap['ielts-preparation'] ?? 1;
    $eeCatId = $catMap['express-entry'] ?? 1;
    $pnpCatId = $catMap['pnp-programs'] ?? 1;
    $caImmigrationId = $catMap['canada-immigration'] ?? 1;

    // 1. Seed 8 new courses (total courses will be 12)
    $newCourses = [
        [
            'cat_id' => $ieltsCatId,
            'title' => 'IELTS Listening Vocabulary & Spelling Booster',
            'slug' => 'ielts-listening-vocabulary-booster',
            'thumbnail' => 'listening_thumb.jpg',
            'desc' => 'Master the spelling rules, map labeling tricks, and common distractors that prevent students from achieving Band 9 in IELTS Listening.',
            'price' => 299.00
        ],
        [
            'cat_id' => $ieltsCatId,
            'title' => 'CELPIP General Practice & Evaluation Masterclass',
            'slug' => 'celpip-general-practice-evaluation',
            'thumbnail' => 'celpip_thumb.jpg',
            'desc' => 'Alternative to IELTS! Learn the structure of the CELPIP test, computer-based exam templates, and speaking response timing.',
            'price' => 599.00
        ],
        [
            'cat_id' => $pnpCatId,
            'title' => 'Saskatchewan SINP Express Entry & Occupation In-Demand Guide',
            'slug' => 'saskatchewan-sinp-occupation-indemand',
            'thumbnail' => 'sinp_thumb.jpg',
            'desc' => 'Apply to Saskatchewan PNP without a job offer. Step-by-step guidance on creating the SINP EOI profile and calculations of SINP grid points.',
            'price' => 899.00
        ],
        [
            'cat_id' => $caImmigrationId,
            'title' => 'Atlantic Immigration Program (AIP) Complete Roadmap',
            'slug' => 'atlantic-immigration-program-aip',
            'thumbnail' => 'aip_thumb.jpg',
            'desc' => 'Learn how to migrate to Nova Scotia, New Brunswick, PEI, or Newfoundland. A directory of designated employers and endorsement steps.',
            'price' => 699.00
        ],
        [
            'cat_id' => $eeCatId,
            'title' => 'Federal Skilled Trades (FST) Program Overview',
            'slug' => 'federal-skilled-trades-fst',
            'thumbnail' => 'fst_thumb.jpg',
            'desc' => 'Immigration pathway for electricians, plumbers, chefs, and mechanics. Learn certification requirements and job offer exemptions.',
            'price' => 499.00
        ],
        [
            'cat_id' => $caImmigrationId,
            'title' => 'Spousal Sponsorship Application Step-by-Step Walkthrough',
            'slug' => 'spousal-sponsorship-canada',
            'thumbnail' => 'spousal_thumb.jpg',
            'desc' => 'Complete checklist for sponsor eligibility, relationship proofs, dynamic questionnaires, inland vs outland choices, and common mistakes.',
            'price' => 1499.00
        ],
        [
            'cat_id' => $caImmigrationId,
            'title' => 'Super Visa for Parents & Grandparents Eligibility Guide',
            'slug' => 'super-visa-parents-grandparents',
            'thumbnail' => 'supervisa_thumb.jpg',
            'desc' => 'How to bring your parents/grandparents to Canada for up to 5 years per visit. Income thresholds (LICO), medical insurance options, and invitation letters.',
            'price' => 399.00
        ],
        [
            'cat_id' => $caImmigrationId,
            'title' => 'LMIA Exemption Pathways & Canadian Work Permits',
            'slug' => 'lmia-exemption-work-permits',
            'thumbnail' => 'lmia_exemption_thumb.jpg',
            'desc' => 'Explore Intra-Company Transfers (ICT), Significant Benefit work permits, and Francophone Mobility exemptions to work in Canada without LMIA.',
            'price' => 1199.00
        ]
    ];
    
    $insertCourse = $pdo->prepare("INSERT INTO courses (category_id, title, slug, thumbnail, description, price, status) VALUES (?, ?, ?, ?, ?, ?, 'Published') ON DUPLICATE KEY UPDATE description = ?, price = ?");
    $insertSec = $pdo->prepare("INSERT INTO course_sections (course_id, title, sort_order) VALUES (?, ?, ?)");
    $insertVid = $pdo->prepare("INSERT INTO course_videos (section_id, course_id, title, video_url, video_source, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($newCourses as $c) {
        $insertCourse->execute([
            $c['cat_id'], $c['title'], $c['slug'], $c['thumbnail'], $c['desc'], $c['price'],
            $c['desc'], $c['price']
        ]);
        
        $courseId = $pdo->lastInsertId();
        if ($courseId > 0) {
            // Seed a default lesson section
            $insertSec->execute([$courseId, 'Section 1: General Introduction', 1]);
            $secId = $pdo->lastInsertId();
            $insertVid->execute([$secId, $courseId, 'Welcome & Class Dashboard Tour', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 1]);
            $insertVid->execute([$secId, $courseId, 'Resource Downloads & Checklists', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 2]);
        }
        echo "Mock Course '{$c['title']}' added.<br>";
    }
    
    // 2. Seed 8 new webinars (total webinars will be 11)
    $newWebinars = [
        [
            'title' => 'BC PNP Draws & Tech Stream Weekly Update',
            'desc' => 'Review of the latest BC PNP draws, points threshold trends, and fast-track processing for tech and healthcare professions.',
            'date' => date('Y-m-d', strtotime('+4 days')),
            'time' => '16:00:00',
            'price' => 199.00
        ],
        [
            'title' => 'Canadian Format Resume & Cover Letter Clinic',
            'desc' => 'Learn how to write ATS-friendly resumes for Canadian employers, format references, and state work permissions clearly.',
            'date' => date('Y-m-d', strtotime('+6 days')),
            'time' => '18:30:00',
            'price' => 99.00
        ],
        [
            'title' => 'WES Evaluation Issues & Document Submission Support',
            'desc' => 'Live session answering how to submit university transcripts, handle secondary education evaluations, and request updates from WES.',
            'date' => date('Y-m-d', strtotime('+8 days')),
            'time' => '14:00:00',
            'price' => 49.00
        ],
        [
            'title' => 'LMIA Work Permits & Job Search Strategies',
            'desc' => 'Detailed session on dual intent visas, Job Bank registrations, identifying scams, and helping employers file LMIA applications.',
            'date' => date('Y-m-d', strtotime('+9 days')),
            'time' => '19:00:00',
            'price' => 249.00
        ],
        [
            'title' => 'Startup Visa (SUV) Program Complete Overview',
            'desc' => 'Immigrate to Canada as an entrepreneur. Learn about Designated Venture Capital funds, Angel groups, Business Incubator pitch tips.',
            'date' => date('Y-m-d', strtotime('+11 days')),
            'time' => '20:00:00',
            'price' => 499.00
        ],
        [
            'title' => 'IELTS Writing Task 1 General vs Academic',
            'desc' => 'Master the structural differences: formal/informal letters for General test, and reports/chart descriptors for Academic test.',
            'date' => date('Y-m-d', strtotime('+13 days')),
            'time' => '10:00:00',
            'price' => 129.00
        ],
        [
            'title' => 'Biometrics, Medicals & PCC Submission Guidelines',
            'desc' => 'Live checklist guidelines for medical exams, panel physician details, police clearance certifications (PCC), and biometric instructions.',
            'date' => date('Y-m-d', strtotime('+14 days')),
            'time' => '15:30:00',
            'price' => 79.00
        ],
        [
            'title' => 'Post-Graduate Work Permit (PGWP) Rules 2026',
            'desc' => 'Important overview of graduation requirements, field of study filters for college students, and hours extension updates.',
            'date' => date('Y-m-d', strtotime('+18 days')),
            'time' => '12:00:00',
            'price' => 149.00
        ]
    ];
    
    $insertWebinar = $pdo->prepare("INSERT INTO webinars (title, description, date, time, price, status) VALUES (?, ?, ?, ?, ?, 'Active')");
    foreach ($newWebinars as $w) {
        $insertWebinar->execute([$w['title'], $w['desc'], $w['date'], $w['time'], $w['price']]);
        echo "Mock Webinar '{$w['title']}' added.<br>";
    }
    
    // 3. Seed 8 new events (total events will be 11)
    $newEvents = [
        [
            'title' => 'British Columbia PNP Tech Draw Consultation',
            'desc' => 'Meet with tech recruiters and advisors to assess eligibility for the BC tech stream nomination.',
            'date' => date('Y-m-d', strtotime('+5 days'))
        ],
        [
            'title' => 'LMIA Employer Matchmaking Conference',
            'desc' => 'An online meetup linking Canadian employers with LMIA job vacancies to skilled international candidates.',
            'date' => date('Y-m-d', strtotime('+8 days'))
        ],
        [
            'title' => 'IELTS Speaking Live Mock Assessment Session 2',
            'desc' => 'Round 2 of our mock examiner sessions. Watch candidates get evaluated live and learn scoring parameters.',
            'date' => date('Y-m-d', strtotime('+11 days'))
        ],
        [
            'title' => 'Alberta Advantage Immigration Program (AAIP) Fair',
            'desc' => 'Information session regarding Alberta Express Entry pathway, Rural Renewal Stream, and Tourism & Hospitality streams.',
            'date' => date('Y-m-d', strtotime('+14 days'))
        ],
        [
            'title' => 'Student Visa to PR Pathway Counseling',
            'desc' => 'Interactive career mapping outlining college choices, PR pathways, and provincial nominee benefits for study permit applicants.',
            'date' => date('Y-m-d', strtotime('+16 days'))
        ],
        [
            'title' => 'WES and ECA Document Submission Support Clinic',
            'desc' => 'One-on-one document screening to help candidates verify their transcripts match WES evaluation formats.',
            'date' => date('Y-m-d', strtotime('+20 days'))
        ],
        [
            'title' => 'Pre-Departure Orientation for Canada Newcomers',
            'desc' => 'Essential guide for approved PR visa holders. Covers banking setup, SIN activation, finding accommodation, and packing lists.',
            'date' => date('Y-m-d', strtotime('+22 days'))
        ],
        [
            'title' => 'Saskatchewan In-Demand Occupation Draw Assessment',
            'desc' => 'Review of the latest high-demand occupations list in Saskatchewan and PNP profile submission methods.',
            'date' => date('Y-m-d', strtotime('+25 days'))
        ]
    ];
    
    $insertEvent = $pdo->prepare("INSERT INTO events (title, description, date) VALUES (?, ?, ?)");
    foreach ($newEvents as $ev) {
        $insertEvent->execute([$ev['title'], $ev['desc'], $ev['date']]);
        echo "Mock Event '{$ev['title']}' added.<br>";
    }
    
    // 4. Seed 12 support queries (total queries will be 12+)
    $queries = [
        ['name' => 'Aman Mehta', 'email' => 'aman@cava.com', 'mobile' => '9876543211', 'msg' => 'My Razorpay payment for Ontario PNP course was successful but course is locked. Help!', 'status' => 'Pending'],
        ['name' => 'Simran Patel', 'email' => 'simran@cava.com', 'mobile' => '9876543212', 'msg' => 'When will I receive the Zoom meeting link for the live Q&A webinar scheduled on Monday?', 'status' => 'Pending'],
        ['name' => 'Kunal Sharma', 'email' => 'kunal@example.com', 'mobile' => '9876543214', 'msg' => 'Is WES evaluation mandatory for Federal Skilled Trades program? I have a diploma.', 'status' => 'Resolved'],
        ['name' => 'Priya Nair', 'email' => 'priya@example.com', 'mobile' => '9876543215', 'msg' => 'Struggling with IELTS Reading. Do you offer mock reading test evaluations separately?', 'status' => 'Pending'],
        ['name' => 'Deepak Verma', 'email' => 'deepak@example.com', 'mobile' => '9876543216', 'msg' => 'Are your course videos downloadable? I have poor internet connection locally.', 'status' => 'Resolved'],
        ['name' => 'Anjali Gupta', 'email' => 'anjali@example.com', 'mobile' => '9876543217', 'msg' => 'I made a payment for the IELTS masterclass but did not receive any email receipt confirmation.', 'status' => 'Pending'],
        ['name' => 'Vikram Singh', 'email' => 'vikram@example.com', 'mobile' => '9876543218', 'msg' => 'How can I change my registered mobile number? The profile section keeps returning a CSRF error.', 'status' => 'Resolved'],
        ['name' => 'Sania Mirza', 'email' => 'sania@example.com', 'mobile' => '9876543219', 'msg' => 'Does Canada Spousal Sponsorship course include checklist templates for photo proofs?', 'status' => 'Pending'],
        ['name' => 'Rajesh Kumar', 'email' => 'rajesh@cava.com', 'mobile' => '9876543213', 'msg' => 'Are there any hidden costs after paying the ₹99 fee for the live webinars draws analysis?', 'status' => 'Pending'],
        ['name' => 'Neha Sen', 'email' => 'neha@example.com', 'mobile' => '9876543220', 'msg' => 'Do you have any physical campuses in Delhi, or is everything strictly virtual learning?', 'status' => 'Resolved'],
        ['name' => 'Rahul Roy', 'email' => 'rahul@example.com', 'mobile' => '9876543221', 'msg' => 'Is there a refund policy if I accidentally purchased the wrong PNP guide course?', 'status' => 'Pending'],
        ['name' => 'Pooja Hegde', 'email' => 'pooja@example.com', 'mobile' => '9876543222', 'msg' => 'Which IELTS test should I take for Express Entry? General Training or Academic?', 'status' => 'Resolved']
    ];
    
    $insertQuery = $pdo->prepare("INSERT INTO queries (user_id, name, email, mobile_number, query_message, status, resolved_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Get mock user mapping to link query histories
    $userMap = [];
    $stmt = $pdo->query("SELECT id, email FROM users");
    while ($row = $stmt->fetch()) {
        $userMap[$row['email']] = $row['id'];
    }
    
    foreach ($queries as $q) {
        $uId = $userMap[$q['email']] ?? null;
        $resolvedAt = ($q['status'] === 'Resolved') ? date('Y-m-d H:i:s', strtotime('-1 day')) : null;
        $insertQuery->execute([$uId, $q['name'], $q['email'], $q['mobile'], $q['msg'], $q['status'], $resolvedAt]);
        echo "Mock Query from '{$q['name']}' added (Status: {$q['status']}).<br>";
    }
    
    echo "<h3>Seeding complete. Every module now contains 10+ records!</h3>";
    
} catch (Exception $e) {
    echo "<h3>Large Seeding Failed:</h3> " . $e->getMessage();
}
