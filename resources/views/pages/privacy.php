<?php
/**
 * Privacy Policy
 */

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <h2 class="mb-4">Privacy Policy</h2>
                <p class="text-muted mb-4">Last updated: March 2026</p>

                <h5>1. Introduction</h5>
                <p>VisionQuest Services LLC ("we", "us", "our") operates the MyExpenses application ("the Service"). This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our Service. Please read this policy carefully. By using the Service, you consent to the data practices described herein.</p>

                <h5>2. Information We Collect</h5>
                <p>We collect the following types of information:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, and password when you register for an account.</li>
                    <li><strong>Financial Data:</strong> Expense descriptions, amounts, dates, categories, vendor names, and other financial information you voluntarily enter into the Service.</li>
                    <li><strong>Usage Data:</strong> Browser type, access times, pages viewed, and the referring URL to help us understand how our Service is used.</li>
                    <li><strong>Device Information:</strong> Device type, operating system, and browser information for Service optimization.</li>
                </ul>

                <h5>3. How We Use Your Information</h5>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Provide, maintain, and improve the Service.</li>
                    <li>Process and manage your expense records.</li>
                    <li>Generate reports and analytics based on your financial data.</li>
                    <li>Authenticate your identity and secure your account.</li>
                    <li>Send you important notifications about the Service.</li>
                    <li>Respond to your support requests and inquiries.</li>
                </ul>

                <h5>4. Data Storage and Security</h5>
                <p>Your data is stored on secure servers with industry-standard encryption. We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
                <ul>
                    <li>Encryption of data in transit (HTTPS/TLS).</li>
                    <li>Hashed and salted password storage.</li>
                    <li>Regular security audits and updates.</li>
                    <li>Access controls limiting data access to authorized personnel only.</li>
                </ul>

                <h5>5. Data Sharing</h5>
                <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
                <ul>
                    <li><strong>With your consent:</strong> When you explicitly authorize us to share information.</li>
                    <li><strong>Legal requirements:</strong> When required by law, regulation, or legal process.</li>
                    <li><strong>Service providers:</strong> With trusted third-party service providers who assist us in operating the Service, subject to confidentiality agreements.</li>
                    <li><strong>Business transfers:</strong> In connection with a merger, acquisition, or sale of assets.</li>
                </ul>

                <h5>6. Data Retention</h5>
                <p>We retain your personal information and financial data for as long as your account is active or as needed to provide the Service. You may request deletion of your account and associated data at any time. Upon account deletion, we will remove your data within 30 days, except where retention is required by law.</p>

                <h5>7. Your Rights</h5>
                <p>You have the right to:</p>
                <ul>
                    <li><strong>Access</strong> your personal information stored in the Service.</li>
                    <li><strong>Correct</strong> any inaccurate or incomplete data.</li>
                    <li><strong>Export</strong> your data in standard formats (CSV, PDF).</li>
                    <li><strong>Delete</strong> your account and all associated data.</li>
                    <li><strong>Opt out</strong> of non-essential communications.</li>
                </ul>

                <h5>8. Cookies</h5>
                <p>The Service uses session cookies to maintain your authenticated state and preferences (such as language settings). These cookies are essential for the Service to function and are not used for tracking or advertising purposes.</p>

                <h5>9. Children's Privacy</h5>
                <p>The Service is not intended for use by individuals under the age of 13. We do not knowingly collect personal information from children under 13. If we discover that a child under 13 has provided us with personal information, we will promptly delete it.</p>

                <h5>10. Changes to This Policy</h5>
                <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date. Your continued use of the Service after any changes constitutes acceptance of the updated policy.</p>

                <h5>11. Contact Us</h5>
                <p>If you have any questions or concerns about this Privacy Policy, please contact us at:</p>
                <p>
                    <strong>VisionQuest Services LLC</strong><br>
                    Email: support@visionquestservices.com
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title   = 'Privacy Policy';
require VIEW_PATH . '/layouts/app.php';
?>
