<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; color: #333333;">
    <table role="presentation"
        style="width: 100%; border-collapse: collapse; border: 0; border-spacing: 0; background-color: #f9f9f9;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation"
                    style="width: 600px; border-collapse: collapse; border: 0; border-spacing: 0; background-color: #ffffff; border-radius: 6px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td
                            style="padding: 30px 30px 20px 30px; text-align: center; background-color: #4361ee; border-radius: 6px 6px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">Email Verification
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;">Dear <span
                                    style="font-weight: 600;">{{ $user->name }}</span>,</p>
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;">Thank you for registering.
                                Please use the OTP below to verify your email address:</p>

                            <!-- OTP Box -->
                            <div
                                style="background-color: #f1f5fe; border: 1px solid #d1dffb; border-radius: 6px; padding: 20px; margin: 25px 0; text-align: center;">
                                <p
                                    style="margin: 0 0 10px 0; font-size: 14px; color: #4361ee; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                    Your Verification Code</p>
                                <p
                                    style="margin: 0; font-size: 36px; font-weight: 700; letter-spacing: 5px; color: #333333;">
                                    {{ $otp }}</p>
                            </div>

                            <p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.5; color: #666666;"><strong
                                    style="color: #ff3c00;">Important:</strong> This OTP is valid for 10 minutes only.
                            </p>

                            <p style="margin: 30px 0 10px 0; font-size: 16px; line-height: 1.5;">If you didn't request
                                this verification, please ignore this email.</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="padding: 30px; border-top: 1px solid #eeeeee; background-color: #fafafa; border-radius: 0 0 6px 6px;">
                            <table role="presentation"
                                style="width: 100%; border-collapse: collapse; border: 0; border-spacing: 0;">
                                <tr>
                                    <td style="padding: 0; text-align: center;">
                                        <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #666666;">
                                            Thanks,<br>The Box4pflege Team</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 20px 0 0 0; text-align: center;">
                                        <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #999999;">This is
                                            an automated email. Please do not reply to this message.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Legal footer -->
                <table role="presentation"
                    style="width: 600px; border-collapse: collapse; border: 0; border-spacing: 0;">
                    <tr>
                        <td style="padding: 20px 0; text-align: center;">
                            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #999999;">
                                &copy; {{ date('Y') }} Box4pflege. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
