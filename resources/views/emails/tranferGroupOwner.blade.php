<!doctype html>
<html lang="en-US">



<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>Reset Password Email Template</title>
    <meta name="description" content="Reset Password Email Template.">
    <style type="text/css">
        a:hover {
            text-decoration: underline !important;
        }

        .content ul {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        ul li {
            margin-bottom: 10px;
            font-size: 16px;
            list-style: none;
        }

        ul li strong {
            font-weight: bold;
        }
    </style>


</head>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
    <!--100% body table-->
    <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
        style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
        <tr>
            <td>
                <table style="background-color: #f2f3f8; max-width:670px;  margin:0 auto;" width="100%" border="0"
                    align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                            <a href="https://meraki-frontend-dos2.onrender.com" title="logo" target="_blank"
                                style="color:#40c057; text-decoration:none; font-size:2rem;">
                                ΜΣRΛΚΙ
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                style="max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 35px;">
                                        <p>
                                            <strong>Hi {{$details['first_name']}},</strong>
                                        </p>
                                        <h1
                                            style="color:#1e1e2d; font-weight:500; margin:0;font-size:24px;font-family:'Rubik',sans-serif;">
                                            You've been tranfer to be the new owner of the group
                                        </h1>

                                        <ul>
                                            <li>
                                                <strong>Group Name:</strong> {{$details['group_name']}}
                                            </li>
                                            <li>
                                                <strong>Group ID:</strong> {{$details['group_id']}}
                                            </li>
                                            <li>
                                                <strong>Previous Owner:</strong> {{$details['fromEmail']}}
                                            </li>
                                        </ul>
                                        <p style="color:#455056; font-size:15px;line-height:24px; margin:0;">
                                            You can now manage the group and its members.
                                        </p>
                                        <a href="https://meraki-frontend-dos2.onrender.com/group/{{$details['group_id']}}"
                                            target="_blank"
                                            style="background:#40c057;text-decoration:none !important; font-weight:500; margin-top:35px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 24px;display:inline-block;border-radius:50px;">
                                            Go to group
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!--/100% body table-->
</body>

</html>