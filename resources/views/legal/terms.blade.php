<x-guest-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Terms of Service</h1>
                    
                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            <strong>Last updated:</strong> {{ date('F j, Y') }}
                        </p>

                        <h2>1. Acceptance of Terms</h2>
                        <p>
                            By accessing and using {{ config('app.name') }}, you accept and agree to be bound by 
                            the terms and provision of this agreement. If you do not agree to abide by the above, 
                            please do not use this service.
                        </p>

                        <h2>2. Description of Service</h2>
                        <p>
                            {{ config('app.name') }} is a Naira-settled binary prediction market platform that allows 
                            users to make predictions on various events and outcomes. Users can stake money on their 
                            predictions and potentially earn returns based on the accuracy of their predictions.
                        </p>

                        <h2>3. User Eligibility</h2>
                        <p>To use our service, you must:</p>
                        <ul>
                            <li>Be at least 18 years of age</li>
                            <li>Be legally capable of entering into binding contracts</li>
                            <li>Not be prohibited from using our services under applicable laws</li>
                            <li>Complete our KYC (Know Your Customer) verification process</li>
                            <li>Provide accurate and truthful information</li>
                        </ul>

                        <h2>4. Account Registration and Security</h2>
                        <p>
                            You are responsible for maintaining the confidentiality of your account credentials 
                            and for all activities that occur under your account. You must notify us immediately 
                            of any unauthorized use of your account.
                        </p>

                        <h2>5. Prediction Markets and Stakes</h2>
                        <p>
                            All predictions and stakes are final once submitted. Market outcomes are determined 
                            based on reliable sources and our resolution criteria. We reserve the right to void 
                            markets in cases of technical errors or unforeseen circumstances.
                        </p>

                        <h2>6. Financial Terms</h2>
                        <ul>
                            <li>All transactions are conducted in Nigerian Naira (NGN)</li>
                            <li>Minimum and maximum stake amounts apply to all markets</li>
                            <li>Withdrawal requests are subject to verification and processing times</li>
                            <li>Platform fees may apply to certain transactions</li>
                            <li>Winnings are calculated based on market odds at the time of stake placement</li>
                        </ul>

                        <h2>7. Prohibited Activities</h2>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Use the platform for any illegal or unauthorized purpose</li>
                            <li>Attempt to manipulate market outcomes or engage in fraudulent activity</li>
                            <li>Create multiple accounts to circumvent limits or restrictions</li>
                            <li>Use automated systems or bots to place stakes</li>
                            <li>Share your account credentials with others</li>
                            <li>Engage in money laundering or other financial crimes</li>
                        </ul>

                        <h2>8. Risk Disclosure</h2>
                        <p>
                            <strong>Important:</strong> Prediction markets involve financial risk. You may lose 
                            some or all of your staked amounts. Only stake money you can afford to lose. 
                            Past performance does not guarantee future results.
                        </p>

                        <h2>9. Platform Availability</h2>
                        <p>
                            We strive to maintain platform availability but do not guarantee uninterrupted service. 
                            We may suspend or terminate services for maintenance, updates, or other operational reasons.
                        </p>

                        <h2>10. Intellectual Property</h2>
                        <p>
                            All content, features, and functionality of the platform are owned by us and are 
                            protected by copyright, trademark, and other intellectual property laws.
                        </p>

                        <h2>11. Limitation of Liability</h2>
                        <p>
                            To the maximum extent permitted by law, we shall not be liable for any indirect, 
                            incidental, special, consequential, or punitive damages arising from your use of the platform.
                        </p>

                        <h2>12. Dispute Resolution</h2>
                        <p>
                            Any disputes arising from these terms shall be resolved through binding arbitration 
                            in accordance with Nigerian law. The courts of Nigeria shall have exclusive jurisdiction.
                        </p>

                        <h2>13. Termination</h2>
                        <p>
                            We may terminate or suspend your account at any time for violation of these terms. 
                            Upon termination, your right to use the platform ceases immediately.
                        </p>

                        <h2>14. Changes to Terms</h2>
                        <p>
                            We reserve the right to modify these terms at any time. Material changes will be 
                            communicated to users via email or platform notifications.
                        </p>

                        <h2>15. Contact Information</h2>
                        <p>
                            For questions about these terms, please contact us at:
                        </p>
                        <ul>
                            <li>Email: {{ env('SUPPORT_EMAIL', 'legal@example.com') }}</li>
                            <li>Address: [Your Business Address]</li>
                        </ul>

                        <p class="mt-8 text-sm text-gray-600 dark:text-gray-400">
                            By using {{ config('app.name') }}, you acknowledge that you have read, 
                            understood, and agree to be bound by these Terms of Service.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
