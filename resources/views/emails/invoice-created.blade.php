<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
        }
        .content {
            margin-bottom: 30px;
        }
        .invoice-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3490dc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice {{ $sale->invoice_number }}</h1>
        </div>

        <div class="content">
            <p>Dear {{ $sale->client->company_name }},</p>

            <p>We hope this email finds you well. We are pleased to inform you that your invoice has been created and is now available.</p>

            <div class="invoice-details">
                <p><strong>Invoice Number:</strong> {{ $sale->invoice_number }}</p>
                <p><strong>Date:</strong> {{ $sale->invoice_date->format('d/m/Y') }}</p>
                <p><strong>Amount Due:</strong> R {{ number_format($sale->calculateTotal() * 1.15, 2) }}</p>
            </div>

            <p>You can view and download your invoice by clicking the button below:</p>

            <p>
                <a href="{{ url('/client/sales/'.$sale->id) }}" class="button">View Invoice</a>
            </p>

            <p>Please note that payment is due within 30 days of the invoice date.</p>

            <p>
                If you have any questions or concerns regarding this invoice, please don't hesitate to contact us.
            </p>

            <p>Thank you for your business!</p>
        </div>

        <div class="footer">
            <p>
                Your Company Name<br>
                Your Address<br>
                Your Contact Information
            </p>
        </div>
    </div>
</body>
</html>
