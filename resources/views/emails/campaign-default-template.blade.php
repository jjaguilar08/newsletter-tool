@php
    // Palette sampled from design-preference/newsletter-mockup.png.
    $cream = '#F7F1E6';
    $creamFooter = '#EFE6D3';
    $terracotta = '#C1793F';
    $creamText = '#FBF3E6';
    $darkText = '#2A2118';
    $mutedOnBanner = '#EAD9C4';
    $mutedGray = '#6B6259';
    $sage = '#EAF3DE';
    $sageStar = '#8FA377';
    $sageAvatar = '#C7D4B4';
    $serif = "Georgia, 'Times New Roman', serif";
    $sans = 'Helvetica, Arial, sans-serif';

    // Shipped with the app under public/images (not the runtime uploads
    // disk), since these are static template seed assets, not user content.
    $image1 = asset('images/newsletter-template/sample-1.png');
    $image2 = asset('images/newsletter-template/sample-2.png');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
</head>
<body style="margin: 0; padding: 0; background-color: {{ $cream }}; font-family: {{ $sans }};">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: {{ $cream }};">
        <tr>
            <td align="center" style="padding: 0;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width: 600px; max-width: 100%; background-color: {{ $cream }};">

                    {{-- Header bar --}}
                    <tr>
                        <td style="background-color: {{ $cream }}; padding: 20px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" valign="middle" width="50%">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td valign="middle" width="32" style="width: 32px;">
                                                    <table role="presentation" width="32" height="32" cellpadding="0" cellspacing="0" border="0" style="width: 32px; height: 32px; background-color: {{ $terracotta }}; border-radius: 50%;">
                                                        <tr>
                                                            <td width="32" height="32" style="width: 32px; height: 32px; line-height: 32px; font-size: 1px;">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td valign="middle" style="padding-left: 10px; font-family: {{ $serif }}; font-size: 18px; font-weight: bold; color: {{ $darkText }}; white-space: nowrap;">
                                                    Brandname
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right" valign="middle" width="50%" style="font-family: {{ $sans }}; font-size: 13px; color: {{ $mutedGray }};">
                                        <a href="#" style="color: {{ $mutedGray }}; text-decoration: none;">View in browser</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Banner block --}}
                    <tr>
                        <td align="center" style="background-color: {{ $terracotta }}; padding: 44px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="font-family: {{ $serif }}; font-size: 32px; line-height: 38px; font-weight: bold; color: {{ $creamText }};">
                                        This Week's Highlights
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 10px; font-family: {{ $sans }}; font-size: 15px; line-height: 22px; color: {{ $mutedOnBanner }};">
                                        A short roundup of what's new, curated just for you.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Intro paragraph --}}
                    <tr>
                        <td style="background-color: {{ $cream }}; padding: 28px 32px 8px 32px; font-family: {{ $sans }}; font-size: 15px; line-height: 23px; color: {{ $darkText }};">
                            Hi there &mdash; here's a look at what's happening this week. Share the
                            update, announcement, or story you want your subscribers to read this
                            issue.
                        </td>
                    </tr>

                    {{-- Content block 1 --}}
                    <tr>
                        <td style="background-color: {{ $cream }}; padding: 24px 32px 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <img
                                            src="{{ $image1 }}"
                                            alt="Feature image"
                                            width="536"
                                            style="display: block; width: 100%; max-width: 536px; height: auto; border: 0; border-radius: 10px;"
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 18px; font-family: {{ $serif }}; font-size: 20px; line-height: 26px; font-weight: bold; color: {{ $darkText }};">
                                        Lorem ipsum dolor sit amet
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 8px; font-family: {{ $sans }}; font-size: 14px; line-height: 21px; color: {{ $mutedGray }};">
                                        Consectetur adipiscing elit, sed do eiusmod tempor incididunt
                                        ut labore et dolore magna aliqua ut enim ad minim veniam.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 12px; font-family: {{ $sans }}; font-size: 14px; font-weight: bold;">
                                        <a href="#" style="color: {{ $terracotta }}; text-decoration: none;">Read more &rarr;</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="border-top: 1px solid #E3D8C4; font-size: 1px; line-height: 1px;">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Content block 2 --}}
                    <tr>
                        <td style="background-color: {{ $cream }}; padding: 28px 32px 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <img
                                            src="{{ $image2 }}"
                                            alt="Feature image"
                                            width="536"
                                            style="display: block; width: 100%; max-width: 536px; height: auto; border: 0; border-radius: 10px;"
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 18px; font-family: {{ $serif }}; font-size: 20px; line-height: 26px; font-weight: bold; color: {{ $darkText }};">
                                        Ut enim ad minima veniam
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 8px; font-family: {{ $sans }}; font-size: 14px; line-height: 21px; color: {{ $mutedGray }};">
                                        Quis nostrum exercitationem ullam corporis suscipit
                                        laboriosam, nisi ut aliquid ex ea commodi consequatur.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 12px; padding-bottom: 36px; font-family: {{ $sans }}; font-size: 14px; font-weight: bold;">
                                        <a href="#" style="color: {{ $terracotta }}; text-decoration: none;">Read more &rarr;</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Testimonial section --}}
                    <tr>
                        <td align="center" style="background-color: {{ $sage }}; padding: 40px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="font-family: {{ $sans }}; font-size: 20px; letter-spacing: 4px; color: {{ $sageStar }};">
                                        &#9733;&#9733;&#9733;&#9733;&#9733;
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 18px; font-family: {{ $serif }}; font-size: 19px; line-height: 28px; font-weight: bold; color: {{ $darkText }};">
                                        &ldquo;Sed ut perspiciatis unde omnis iste natus error sit
                                        voluptatem &mdash; genuinely useful every time.&rdquo;
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 20px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td valign="middle" width="36" style="width: 36px;">
                                                    <table role="presentation" width="36" height="36" cellpadding="0" cellspacing="0" border="0" style="width: 36px; height: 36px; background-color: {{ $sageAvatar }}; border-radius: 50%;">
                                                        <tr>
                                                            <td width="36" height="36" style="width: 36px; height: 36px; line-height: 36px; font-size: 1px;">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td valign="middle" align="left" style="padding-left: 10px; font-family: {{ $sans }}; font-size: 13px; line-height: 18px; color: {{ $darkText }};">
                                                    <strong>Jane Doe</strong><br>
                                                    <span style="color: {{ $mutedGray }};">Customer</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CTA section --}}
                    <tr>
                        <td align="center" style="background-color: {{ $cream }}; padding: 44px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="font-family: {{ $serif }}; font-size: 24px; line-height: 30px; font-weight: bold; color: {{ $darkText }};">
                                        Want to see more?
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 8px; font-family: {{ $sans }}; font-size: 15px; line-height: 22px; color: {{ $mutedGray }};">
                                        Explore everything we've put together this week.
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 24px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="border-radius: 24px; background-color: {{ $terracotta }};">
                                                    <a
                                                        href="#"
                                                        target="_blank"
                                                        style="display: inline-block; padding: 14px 36px; font-family: {{ $sans }}; font-size: 15px; font-weight: bold; line-height: 20px; color: {{ $creamText }}; text-decoration: none; border-radius: 24px;"
                                                    >
                                                        Explore More
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="background-color: {{ $creamFooter }}; padding: 32px 32px; border-top: 1px solid #E3D8C4;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    @foreach (['&#9993;', 'X', '&#128247;', '&#128279;'] as $icon)
                                        <td style="padding: 0 6px;">
                                            <table role="presentation" width="32" height="32" cellpadding="0" cellspacing="0" border="0" style="width: 32px; height: 32px; background-color: #ffffff; border: 1px solid #E3D8C4; border-radius: 50%;">
                                                <tr>
                                                    <td align="center" valign="middle" width="32" height="32" style="width: 32px; height: 32px; font-family: {{ $sans }}; font-size: 13px; color: {{ $darkText }};">
                                                        {!! $icon !!}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-top: 20px; font-family: {{ $sans }}; font-size: 12px; line-height: 18px; color: {{ $mutedGray }};">
                                        Brandname &middot; 123 Placeholder Ave, Some City, ST 00000
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 6px; font-family: {{ $sans }}; font-size: 12px; line-height: 18px; color: {{ $mutedGray }};">
                                        <a href="#" style="color: {{ $mutedGray }}; text-decoration: underline;">Unsubscribe</a>
                                        &nbsp;&middot;&nbsp;
                                        <a href="#" style="color: {{ $mutedGray }}; text-decoration: underline;">Manage preferences</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
