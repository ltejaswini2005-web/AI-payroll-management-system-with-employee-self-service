<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIAssistantService
{
    /**
     * Generate an AI response using the configured provider or fallback engine.
     *
     * @param  string  $userMessage
     * @param  array   $hrContext
     * @param  User    $user
     * @return string
     */
    public function generateReply(string $userMessage, array $hrContext, User $user): string
    {
        $provider = config('ai-assistant.provider', 'openai');

        // Check if user is asking about someone else (Anti-data leak security guard)
        if ($this->detectsCrossEmployeeInquiry($userMessage, $hrContext)) {
            return "🔒 **Privacy Notice**: Under company HR data protection policies, I can only provide information regarding your own employment records. I am not authorized to disclose the personal, salary, or attendance details of other colleagues.";
        }

        // 1. Try OpenAI if configured
        if ($provider === 'openai') {
            $apiKey = config('ai-assistant.openai.api_key');
            if (!empty($apiKey)) {
                $response = $this->callOpenAI($userMessage, $hrContext['context_text'], $apiKey);
                if ($response !== null) {
                    return $response;
                }
            }
        }

        // 2. Try Gemini if configured
        if ($provider === 'gemini') {
            $geminiKey = config('ai-assistant.gemini.api_key');
            if (!empty($geminiKey)) {
                $response = $this->callGemini($userMessage, $hrContext['context_text'], $geminiKey);
                if ($response !== null) {
                    return $response;
                }
            }
        }

        // 3. Built-in Intelligent HR Assistant Engine (Deterministic Fallback)
        return $this->smartHRFallbackReply($userMessage, $hrContext);
    }

    /**
     * Detect cross-employee inquiry attempts.
     */
    private function detectsCrossEmployeeInquiry(string $message, array $hrContext): bool
    {
        $lower = strtolower($message);
        $suspiciousKeywords = [
            'salary of', 'salary for', 'payslip of', 'attendance of', 'leaves of',
            'john', 'alex', 'sarah', 'michael', 'other employee', 'another employee',
            'colleague', 'someone else', 'everyone', 'all employees', 'other staff'
        ];

        // If user is asking for "all employees" or "salary of [person]" and not an authorized query
        foreach ($suspiciousKeywords as $term) {
            if (str_contains($lower, $term) && !str_contains($lower, 'my')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Call OpenAI Chat Completion API.
     */
    private function callOpenAI(string $userMessage, string $contextText, string $apiKey): ?string
    {
        try {
            $model = config('ai-assistant.openai.model', 'gpt-3.5-turbo');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(12)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are an intelligent, friendly HR Assistant inside a Laravel HR & Payroll management portal.\n\n"
                            . $contextText . "\n\n"
                            . "Answer the user concisely and professionally using markdown (bolding, lists). "
                            . "Only state facts present in the context. Never fabricate data.",
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage,
                    ],
                ],
                'temperature' => config('ai-assistant.openai.temperature', 0.4),
                'max_tokens' => config('ai-assistant.openai.max_tokens', 800),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::warning('OpenAI API returned non-200: ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('OpenAI API call failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Call Google Gemini API.
     */
    private function callGemini(string $userMessage, string $contextText, string $apiKey): ?string
    {
        try {
            $model = config('ai-assistant.gemini.model', 'gemini-1.5-flash');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(12)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "You are an intelligent HR Assistant inside an HR & Payroll portal.\n"
                                    . $contextText . "\n\nUser Question: " . $userMessage,
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            Log::warning('Gemini API returned non-200: ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('Gemini API call failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Smart Built-in HR reasoning engine for zero-dependency local demo and fallbacks.
     */
    private function smartHRFallbackReply(string $msg, array $hr): string
    {
        $query = strtolower(trim($msg));
        $emp = $hr['employee'];

        // 1. Leave balance query
        if (str_contains($query, 'leave balance') || str_contains($query, 'leaves left') || str_contains($query, 'remaining leave') || str_contains($query, 'how many leaves') || str_contains($query, 'leave quota')) {
            if (empty($hr['leaves']['balances'])) {
                return "ℹ️ **Leave Balances**: You currently do not have any active leave allocations recorded. Please consult the HR department for policy assignments.";
            }

            $out = "📊 **Your Current Leave Balances**:\n\n";
            foreach ($hr['leaves']['balances'] as $b) {
                $out .= "• **{$b['category']}**: **{$b['remaining']} days remaining** (Allocated: {$b['allocated']}, Used: {$b['availed']})\n";
            }
            $out .= "\n*You can submit a new leave request anytime from the Leaves section.*";
            return $out;
        }

        // 2. Leave applications / status
        if (str_contains($query, 'leave status') || str_contains($query, 'leave application') || str_contains($query, 'my leaves') || str_contains($query, 'applied leave')) {
            if (empty($hr['leaves']['recent_applications'])) {
                return "ℹ️ You haven't submitted any leave applications recently.";
            }

            $out = "📝 **Your Recent Leave Applications**:\n\n";
            foreach ($hr['leaves']['recent_applications'] as $app) {
                $badge = match(strtolower($app['status'])) {
                    'approved' => '✅ Approved',
                    'rejected' => '❌ Rejected',
                    default => '⏳ Pending Approval',
                };
                $out .= "• **{$app['category']}** ({$app['start_date']} to {$app['end_date']}, {$app['days']} days)\n  Status: {$badge}\n  *Reason: {$app['reason']}*\n\n";
            }
            return trim($out);
        }

        // 3. Attendance query
        if (str_contains($query, 'attendance') || str_contains($query, 'working hours') || str_contains($query, 'check in') || str_contains($query, 'check out') || str_contains($query, 'present') || str_contains($query, 'late')) {
            $att = $hr['attendance']['summary'];
            if (empty($att) || ($att['total_tracked_days'] ?? 0) === 0) {
                return "ℹ️ **Attendance**: No recent attendance logs found for your profile. Please use the Attendance Kiosk with Face Recognition to log your check-ins.";
            }

            $out = "⏱️ **Your Attendance Summary (Recent 30 Days)**:\n\n";
            $out .= "• **Days Present**: {$att['present_days']} days\n";
            $out .= "• **Late Arrivals**: {$att['late_days']} days\n";
            $out .= "• **Half Days**: {$att['half_days']} days\n";
            $out .= "• **Total Worked**: {$att['total_worked_hours']} hours\n";

            if (!empty($hr['attendance']['recent_logs'])) {
                $last = $hr['attendance']['recent_logs'][0];
                $out .= "\n**Latest Log ({$last['date']})**:\n";
                $out .= "• Status: **{$last['status']}** | In: `{$last['check_in']}` | Out: `{$last['check_out']}`\n";
            }
            return $out;
        }

        // 4. Payslip / Salary query
        if (str_contains($query, 'salary') || str_contains($query, 'payslip') || str_contains($query, 'pay slip') || str_contains($query, 'net pay') || str_contains($query, 'deduction') || str_contains($query, 'allowance') || str_contains($query, 'earnings')) {
            $p = $hr['payroll'];
            if (!$p) {
                return "ℹ️ **Payroll**: Your payslip records are being processed by HR. Once generated, your complete salary breakdown will appear here.";
            }

            $out = "💰 **Your Latest Payslip Breakdown**:\n\n";
            $out .= "• **Basic Salary**: ₹" . number_format($p['basic_salary'], 2) . "\n";
            $out .= "• **Total Allowances**: +₹" . number_format($p['allowances'], 2) . "\n";
            if ($p['bonuses'] > 0) {
                $out .= "• **Bonuses**: +₹" . number_format($p['bonuses'], 2) . "\n";
            }
            $out .= "• **PF Deduction**: -₹" . number_format($p['pf_deduction'], 2) . "\n";
            $out .= "• **Tax Deduction**: -₹" . number_format($p['tax_deduction'], 2) . "\n";
            $out .= "• **Total Deductions**: -₹" . number_format($p['total_deductions'], 2) . "\n";
            $out .= "━━━━━━━━━━━━━━━━━━━━━━\n";
            $out .= "💵 **Net Payable**: **₹" . number_format($p['net_payable'], 2) . "**\n";
            $out .= "• Payment Status: **{$p['payment_status']}**\n";
            return $out;
        }

        // 5. Provident Fund (PF) query
        if (str_contains($query, 'provident fund') || str_contains($query, 'pf') || str_contains($query, 'epf') || str_contains($query, 'pension')) {
            $pf = $hr['provident_fund'];
            if (!$pf) {
                return "ℹ️ **Provident Fund**: No dedicated Provident Fund account has been registered for your profile yet. Please contact Payroll support.";
            }

            $out = "🏛️ **Provident Fund (PF) Summary**:\n\n";
            $out .= "• **PF Account Number**: `{$pf['account_number']}`\n";
            $out .= "• **Employee Contribution**: {$pf['employee_contribution_rate']}\n";
            $out .= "• **Employer Contribution**: {$pf['employer_contribution_rate']}\n";
            $out .= "• **Account Status**: **{$pf['status']}**\n";
            $out .= "\n*Deductions are automatically credited to your PF ledger during monthly payroll runs.*";
            return $out;
        }

        // 6. Holiday query
        if (str_contains($query, 'holiday') || str_contains($query, 'vacation') || str_contains($query, 'festival') || str_contains($query, 'off day')) {
            if (empty($hr['holidays'])) {
                return "ℹ️ **Upcoming Holidays**: There are no upcoming public holidays scheduled in the calendar at this time.";
            }

            $out = "🎉 **Upcoming Company Holidays**:\n\n";
            foreach ($hr['holidays'] as $h) {
                $tag = $h['is_optional'] ? ' *(Optional)*' : '';
                $out .= "• **{$h['name']}**: {$h['date']}{$tag}\n";
            }
            return $out;
        }

        // 7. Announcements query
        if (str_contains($query, 'announcement') || str_contains($query, 'news') || str_contains($query, 'notice') || str_contains($query, 'circular')) {
            if (empty($hr['announcements'])) {
                return "ℹ️ There are no new announcements published at this time.";
            }

            $out = "📢 **Company Announcements**:\n\n";
            foreach ($hr['announcements'] as $a) {
                $out .= "• **{$a['title']}** ({$a['date']})\n  {$a['content']}\n\n";
            }
            return trim($out);
        }

        // 8. Profile query
        if (str_contains($query, 'who am i') || str_contains($query, 'my profile') || str_contains($query, 'my department') || str_contains($query, 'my code') || str_contains($query, 'my designation')) {
            if (!$emp) {
                return "👤 You are currently logged in with an administrative profile.";
            }

            $out = "👤 **Your Profile Information**:\n\n";
            $out .= "• **Name**: {$emp['name']}\n";
            $out .= "• **Employee Code**: `{$emp['employee_code']}`\n";
            $out .= "• **Department**: {$emp['department']}\n";
            $out .= "• **Designation**: {$emp['designation']}\n";
            $out .= "• **Joining Date**: {$emp['joining_date']}\n";
            $out .= "• **Status**: {$emp['status']}\n";
            return $out;
        }

        // 9. Admin Overview query
        if (!empty($hr['is_admin']) && !empty($hr['system_stats']) && (str_contains($query, 'how many employee') || str_contains($query, 'total employee') || str_contains($query, 'active employee') || str_contains($query, 'company overview') || str_contains($query, 'stats'))) {
            $stats = $hr['system_stats'];
            return "🏢 **Company HR Overview (Admin)**:\n\n"
                . "• **Total Registered Employees**: {$stats['total_employees']}\n"
                . "• **Active Status Employees**: {$stats['active_employees']}\n"
                . "• **Today's Attendance Logs**: {$stats['today_attendance']}\n\n"
                . "*You can manage records through the Employees and Attendance modules.*";
        }

        // 10. Greetings & Help
        $name = $emp['name'] ?? 'there';
        return "👋 Hello **{$name}**! I am your AI HR Assistant.\n\n"
            . "I can help you instantly with:\n"
            . "• **Leave Balances & Requests** (e.g. *'What is my leave balance?'*)\n"
            . "• **Attendance & Hours** (e.g. *'Show my attendance summary'*)\n"
            . "• **Salary & Payslip** (e.g. *'My payslip breakdown'*)\n"
            . "• **Provident Fund (PF)** (e.g. *'Check my PF status'*)\n"
            . "• **Upcoming Holidays & Company News**\n\n"
            . "What would you like to check today?";
    }
}
