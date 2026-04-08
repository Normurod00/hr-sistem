<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; }
        .certificate {
            width: 100%;
            min-height: 700px;
            border: 8px double #E52716;
            padding: 50px;
            text-align: center;
            position: relative;
        }
        .certificate::before {
            content: '';
            position: absolute;
            top: 15px; left: 15px; right: 15px; bottom: 15px;
            border: 2px solid #E52716;
        }
        .logo { font-size: 28px; font-weight: bold; color: #E52716; margin-bottom: 10px; letter-spacing: 3px; }
        .subtitle { font-size: 12px; color: #666; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 2px; }
        .title { font-size: 22px; color: #333; margin-bottom: 30px; }
        .name { font-size: 32px; font-weight: bold; color: #E52716; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #E52716; display: inline-block; }
        .position { font-size: 14px; color: #666; margin-bottom: 30px; }
        .award-type { font-size: 18px; color: #333; margin-bottom: 8px; }
        .award-period { font-size: 14px; color: #666; margin-bottom: 30px; }
        .description { font-size: 12px; color: #555; max-width: 80%; margin: 0 auto 40px; line-height: 1.6; }
        .footer-section { display: flex; justify-content: space-between; margin-top: 40px; padding: 0 40px; }
        .sign-block { text-align: center; }
        .sign-line { width: 180px; border-top: 1px solid #333; margin: 30px auto 5px; }
        .sign-label { font-size: 10px; color: #666; }
        .date { font-size: 11px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="logo">{{ config('app.name', 'HR-System') }}</div>
        <div class="subtitle">Сертификат признания</div>

        <div class="title">Настоящим удостоверяется, что</div>

        <div class="name">{{ $award->employee?->name ?? 'Сотрудник' }}</div>

        @if($award->employee?->employeeProfile)
            <div class="position">{{ $award->employee->employeeProfile->position ?? '' }}, {{ $award->employee->employeeProfile->department ?? '' }}</div>
        @endif

        <div class="award-type">{{ $award->awardType?->label() ?? $award->award_type }}</div>
        <div class="award-period">{{ $award->period ?? '' }}</div>

        @if($award->description)
            <div class="description">{{ $award->description }}</div>
        @endif

        <table style="width: 100%; margin-top: 40px;">
            <tr>
                <td style="text-align: center; width: 50%;">
                    <div class="sign-line"></div>
                    <div class="sign-label">Директор HR</div>
                </td>
                <td style="text-align: center; width: 50%;">
                    <div class="sign-line"></div>
                    <div class="sign-label">Руководитель</div>
                </td>
            </tr>
        </table>

        <div class="date">Дата: {{ $award->created_at?->format('d.m.Y') ?? now()->format('d.m.Y') }}</div>
    </div>
</body>
</html>
