<?php
/**
 * Technoknowledge Backend - Form Email Notifier
 * Sends formatted email alert to admin with 1-click WhatsApp reply button.
 */

declare(strict_types=1);

class FormMailer {
    public static function notify(array $submission, array $config): bool {
        $to = $config['mail']['notify_email'] ?? 'technoknowledge19@gmail.com';
        if (empty($to)) {
            return false;
        }

        $formTitle = $submission['form_label'] ?? 'New Form Submission';
        $name = $submission['name'] ?: 'Visitor';
        $subject = "[$formTitle] New lead from " . $name;

        $wa = FormValidator::formatWhatsApp($submission['whatsapp'] ?? '');
        $waUrl = $wa['wa_url'];
        $cleanPhone = $wa['clean'];

        // Prefilled WhatsApp message
        $waPrefill = "Hello " . urlencode($name) . ", thank you for contacting Technoknowledge regarding " . urlencode($formTitle) . ". How may we assist you today?";
        $waChatLink = $waUrl ? ($waUrl . '?text=' . $waPrefill) : '#';

        // Prepare details list
        $rows = [];
        $fields = [
            'Form Type'       => $submission['form_label'],
            'Contact Name'    => $submission['name'],
            'WhatsApp'        => $submission['whatsapp'],
            'Email'           => $submission['email'],
            'Institution'     => $submission['institution'],
            'Role'            => $submission['role'],
            'City'            => $submission['city'],
            'Child Name'      => $submission['child_name'],
            'Child Age'       => $submission['child_age'],
            'Courses Chosen'  => $submission['courses'],
            'Class Type'      => $submission['class_type'],
            'Program'         => $submission['program'],
            'Interest'        => $submission['interest'],
            'Interests'       => $submission['interests'],
            'I Am'            => $submission['iam'],
            'Subject'         => $submission['subject'],
            'Message'         => $submission['message'],
            'Teachers Count'  => $submission['teachers_count'],
            'Students Count'  => $submission['students_count'],
            'Notes'           => $submission['notes'],
            'Sent from Page'  => $submission['page'],
            'Submission Time' => $submission['created_at'] . ' (PKT)',
        ];

        foreach ($fields as $label => $val) {
            if ($val !== null && trim((string)$val) !== '') {
                $rows[] = '<tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#13294B;width:35%;background-color:#f8fafc;">' . htmlspecialchars($label) . '</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e8f0;color:#334155;">' . nl2br(htmlspecialchars((string)$val)) . '</td>
                </tr>';
            }
        }

        $tableHtml = implode("\n", $rows);

        $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;margin:0;padding:24px;background-color:#f1f5f9;color:#1e293b;">
    <div style="max-width:620px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
        <div style="background-color:#13294B;padding:24px 28px;text-align:left;">
            <div style="color:#FFC21A;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Technoknowledge Lead Alert</div>
            <h1 style="color:#ffffff;margin:0;font-size:22px;font-weight:700;">' . htmlspecialchars($formTitle) . '</h1>
        </div>
        <div style="padding:28px;">
            <p style="margin-top:0;font-size:15px;line-height:1.6;color:#475569;">
                A new submission was received on the website from <strong>' . htmlspecialchars($name) . '</strong>.
            </p>

            ' . ($waUrl ? '
            <div style="margin:20px 0;text-align:center;">
                <a href="' . htmlspecialchars($waChatLink) . '" target="_blank" style="display:inline-block;background-color:#25D366;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:12px 24px;border-radius:8px;box-shadow:0 2px 6px rgba(37,211,102,0.35);">
                    💬 Reply to ' . htmlspecialchars($name) . ' on WhatsApp
                </a>
            </div>
            ' : '') . '

            <table style="width:100%;border-collapse:collapse;margin:24px 0;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;font-size:14px;">
                <tbody>
                    ' . $tableHtml . '
                </tbody>
            </table>

            <div style="background-color:#f8fafc;padding:14px 18px;border-radius:8px;font-size:13px;color:#64748b;margin-top:24px;">
                💡 <strong>Admin tip:</strong> You can manage this lead, change its status, and add follow-up notes in your <a href="' . htmlspecialchars($config['app_url'] . '/backend/admin/') . '" style="color:#13294B;font-weight:600;">Admin Dashboard</a>.
            </div>
        </div>
        <div style="background-color:#f8fafc;padding:16px 28px;text-align:center;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;">
            Technoknowledge (SMC-Pvt) Ltd. • Creating Technology Producers
        </div>
    </div>
</body>
</html>';

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $config['mail']['from_name'] . ' <' . $config['mail']['from_email'] . '>',
            'Reply-To: ' . (!empty($submission['email']) ? $submission['email'] : $config['mail']['from_email']),
            'X-Mailer: PHP/' . phpversion()
        ];

        // Send via mail() or fallback
        try {
            return @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
        } catch (Throwable $e) {
            error_log('Failed to send lead email: ' . $e->getMessage());
            return false;
        }
    }
}
