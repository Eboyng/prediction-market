<x-guest-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Privacy Policy</h1>
                    
                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            <strong>Last updated:</strong> {{ date('F j, Y') }}
                        </p>

                        <h2>1. Information We Collect</h2>
                        <p>
                            We collect information you provide directly to us, such as when you create an account, 
                            make predictions, or contact us for support. This includes:
                        </p>
                        <ul>
                            <li>Personal information (name, email address, phone number)</li>
                            <li>Account credentials and preferences</li>
                            <li>Transaction and payment information</li>
                            <li>KYC verification documents</li>
                            <li>Communication records</li>
                        </ul>

                        <h2>2. How We Use Your Information</h2>
                        <p>We use the information we collect to:</p>
                        <ul>
                            <li>Provide, maintain, and improve our prediction market services</li>
                            <li>Process transactions and send related information</li>
                            <li>Verify your identity and prevent fraud</li>
                            <li>Send technical notices and support messages</li>
                            <li>Comply with legal obligations and regulatory requirements</li>
                        </ul>

                        <h2>3. Information Sharing</h2>
                        <p>
                            We do not sell, trade, or otherwise transfer your personal information to third parties 
                            without your consent, except as described in this policy. We may share information:
                        </p>
                        <ul>
                            <li>With service providers who assist in our operations</li>
                            <li>To comply with legal obligations or government requests</li>
                            <li>To protect our rights and prevent fraud</li>
                            <li>In connection with a business transfer or merger</li>
                        </ul>

                        <h2>4. Data Security</h2>
                        <p>
                            We implement appropriate security measures to protect your personal information against 
                            unauthorized access, alteration, disclosure, or destruction. However, no method of 
                            transmission over the internet is 100% secure.
                        </p>

                        <h2>5. Data Retention</h2>
                        <p>
                            We retain your information for as long as necessary to provide our services and comply 
                            with legal obligations. You may request deletion of your account and associated data 
                            at any time, subject to legal requirements.
                        </p>

                        <h2>6. Your Rights</h2>
                        <p>You have the right to:</p>
                        <ul>
                            <li>Access and update your personal information</li>
                            <li>Request deletion of your data</li>
                            <li>Opt out of marketing communications</li>
                            <li>Request a copy of your data</li>
                        </ul>

                        <h2>7. Cookies and Tracking</h2>
                        <p>
                            We use cookies and similar technologies to improve your experience, analyze usage, 
                            and provide personalized content. You can control cookie settings through your browser.
                        </p>

                        <h2>8. Changes to This Policy</h2>
                        <p>
                            We may update this privacy policy from time to time. We will notify you of any 
                            material changes by posting the new policy on this page and updating the "Last updated" date.
                        </p>

                        <h2>9. Contact Us</h2>
                        <p>
                            If you have any questions about this privacy policy, please contact us at:
                        </p>
                        <ul>
                            <li>Email: {{ env('SUPPORT_EMAIL', 'privacy@example.com') }}</li>
                            <li>Address: [Your Business Address]</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
