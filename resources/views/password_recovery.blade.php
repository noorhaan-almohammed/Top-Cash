<html>
    <body style="font-family: Verdana; color: #333333; font-size: 12px;">
        <table style="width: 600px; margin: 0px auto;">
            <tr style="text-align: center;">
                <td style="border-bottom: solid 1px #cccccc;">
                    <h1 style="margin: 0; font-size: 20px;">
                        <a href="{{ url('/') }}" style="text-decoration:none;color:#333333;">
                            <b>{{ config('app.name') }}</b>
                        </a>
                    </h1>
                    <h2 style="text-align: right; font-size: 14px; margin: 7px 0 10px 0;">Password Recovery</h2>
                </td>
            </tr>
            <tr style="text-align: justify;">
                <td style="padding-top: 15px; padding-bottom: 15px;">
                    Hi {{ $username }},
                    <br /><br />
                    It looks like you requested a password reset. No worries, we've got you covered! Click the link below to set a new password:
                    <br /><br />
                    <a href="{{ $recover_url }}" style="color: #4CAF50;">Reset Your Password</a>
                    <br /><br />
                    If you didn't request this, just ignore this email. Your account is still safe and secure.
                    <br /><br />
                    Cheers,
                    <br />
                    The {{ config('app.name') }} Team
                </td>
            </tr>
            <tr style="text-align: right; color: #777777;">
                <td style="padding-top: 10px; border-top: solid 1px #cccccc;">
                    Stay awesome!
                </td>
            </tr>
        </table>
    </body>
</html>
