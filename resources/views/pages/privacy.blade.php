@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h2 class="fw-bold mb-4">Privacy Policy</h2>
                <p class="text-muted mb-4">Last updated: March 27, 2026</p>

                <h5 class="fw-semibold mt-4">1. Introduction</h5>
                <p>VisionQuest Services LLC ("we", "us", "our") operates VQMoney ("the Service"). This Privacy Policy explains how we collect, use, and protect your information when you use our Service.</p>

                <h5 class="fw-semibold mt-4">2. Information We Collect</h5>
                <p>We collect the following types of information:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, and password when you create an account</li>
                    <li><strong>Financial Data:</strong> Expense descriptions, amounts, dates, categories, vendors, and other data you enter into the Service</li>
                    <li><strong>Usage Data:</strong> Log data, browser type, access times, and pages viewed</li>
                    <li><strong>Uploaded Files:</strong> Receipt images and CSV files you upload to the Service</li>
                </ul>

                <h5 class="fw-semibold mt-4">3. How We Use Your Information</h5>
                <p>We use collected information to:</p>
                <ul>
                    <li>Provide, maintain, and improve the Service</li>
                    <li>Process and organize your expense data</li>
                    <li>Generate reports and analytics</li>
                    <li>Communicate with you about the Service</li>
                    <li>Ensure security and prevent fraud</li>
                </ul>

                <h5 class="fw-semibold mt-4">4. Data Storage and Security</h5>
                <p>Your data is stored on secure servers. We implement industry-standard security measures to protect your information, including encrypted connections (HTTPS), secure password hashing, and regular backups. However, no method of electronic storage is 100% secure.</p>

                <h5 class="fw-semibold mt-4">5. Data Sharing</h5>
                <p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>
                <ul>
                    <li>With your explicit consent</li>
                    <li>To comply with legal obligations</li>
                    <li>To protect our rights or the safety of users</li>
                </ul>

                <h5 class="fw-semibold mt-4">6. Data Retention</h5>
                <p>We retain your data for as long as your account is active or as needed to provide the Service. You may request deletion of your data at any time by contacting us.</p>

                <h5 class="fw-semibold mt-4">7. Your Rights</h5>
                <p>You have the right to:</p>
                <ul>
                    <li>Access your personal data</li>
                    <li>Correct inaccurate data</li>
                    <li>Request deletion of your data</li>
                    <li>Export your data (via CSV export)</li>
                    <li>Withdraw consent at any time</li>
                </ul>

                <h5 class="fw-semibold mt-4">8. Cookies</h5>
                <p>The Service uses session cookies to maintain your login state and preferences (such as language selection). These cookies are essential for the Service to function and are not used for tracking or advertising.</p>

                <h5 class="fw-semibold mt-4">9. Changes to This Policy</h5>
                <p>We may update this Privacy Policy from time to time. We will notify you of significant changes by posting a notice on the Service. Continued use after changes constitutes acceptance.</p>

                <h5 class="fw-semibold mt-4">10. Contact Us</h5>
                <p>If you have questions or concerns about this Privacy Policy, please contact VisionQuest Services LLC.</p>

                <hr class="my-4">
                <p class="text-muted small">&copy; 2026 VisionQuest Services LLC. All rights reserved.</p>
            </div>
        </div>
    </div>
</div>
@endsection
