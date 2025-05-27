<?php

namespace App\Services;

use App\Models\Sale;
use Codedge\Fpdf\Fpdf\Fpdf;

class InvoiceService
{
    protected $fpdf;
    protected $sale;

    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
        $this->fpdf = new Fpdf();
    }

    public function generateInvoicePdf()
    {
        $this->fpdf->AddPage();
        $this->setupDocument();
        $this->addHeader();
        $this->addBillingDetails();
        $this->addInvoiceDetails();
        $this->addProductsTable();
        $this->addTotals();
        $this->addFooter();

        return $this->fpdf;
    }

    private function setupDocument()
    {
        $this->fpdf->SetFont('Arial', '', 12);
        $this->fpdf->SetTitle('Invoice #' . $this->sale->invoice_number);
        $this->fpdf->SetMargins(10, 10, 10);
    }

    private function addHeader()
    {
        // Company Logo (replace with your actual logo path)
        // $this->fpdf->Image('path/to/logo.png', 10, 10, 40);

        // Company Info
        $this->fpdf->SetFont('Arial', 'B', 18);
        $this->fpdf->Cell(0, 10, 'Your Company Name', 0, 1, 'R');

        $this->fpdf->SetFont('Arial', '', 10);
        $this->fpdf->Cell(0, 5, 'Address: Your Company Address', 0, 1, 'R');
        $this->fpdf->Cell(0, 5, 'Phone: Your Phone Number', 0, 1, 'R');
        $this->fpdf->Cell(0, 5, 'Email: your@email.com', 0, 1, 'R');

        $this->fpdf->Ln(10);

        // Invoice Title
        $this->fpdf->SetFont('Arial', 'B', 16);
        $this->fpdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');
    }

    private function addBillingDetails()
    {
        $this->fpdf->SetFont('Arial', 'B', 12);
        $this->fpdf->Cell(0, 10, 'Bill To:', 0, 1);

        $this->fpdf->SetFont('Arial', '', 12);
        $client = $this->sale->client;
        $this->fpdf->Cell(0, 5, $client->company_name, 0, 1);
        $this->fpdf->MultiCell(0, 5, $client->billing_address, 0, 'L');

        if ($client->contact_person) {
            $this->fpdf->Cell(0, 5, 'Contact: ' . $client->contact_person, 0, 1);
        }

        if ($client->phone_number) {
            $this->fpdf->Cell(0, 5, 'Phone: ' . $client->phone_number, 0, 1);
        }

        $this->fpdf->Cell(0, 5, 'Email: ' . $client->user->email, 0, 1);

        $this->fpdf->Ln(10);
    }

    private function addInvoiceDetails()
    {
        $this->fpdf->SetFont('Arial', 'B', 12);
        $this->fpdf->Cell(95, 10, 'Invoice Details', 0, 0);
        $this->fpdf->Cell(95, 10, '', 0, 1);

        $this->fpdf->SetFont('Arial', '', 12);

        $this->fpdf->Cell(40, 5, 'Invoice Number:', 0, 0);
        $this->fpdf->Cell(55, 5, $this->sale->invoice_number, 0, 0);
        $this->fpdf->Cell(95, 5, '', 0, 1);

        $this->fpdf->Cell(40, 5, 'Invoice Date:', 0, 0);
        $this->fpdf->Cell(55, 5, $this->sale->invoice_date->format('Y-m-d'), 0, 0);
        $this->fpdf->Cell(95, 5, '', 0, 1);

        $this->fpdf->Ln(10);
    }

    private function addProductsTable()
    {
        // Table Header
        $this->fpdf->SetFillColor(220, 220, 220);
        $this->fpdf->SetFont('Arial', 'B', 12);
        $this->fpdf->Cell(100, 10, 'Product', 1, 0, 'C', true);
        $this->fpdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
        $this->fpdf->Cell(30, 10, 'Unit Price', 1, 0, 'C', true);
        $this->fpdf->Cell(30, 10, 'Total', 1, 1, 'C', true);

        // Table Content
        $this->fpdf->SetFont('Arial', '', 12);

        foreach ($this->sale->products as $product) {
            $this->fpdf->Cell(100, 10, $product->name, 1, 0);
            $this->fpdf->Cell(30, 10, $product->pivot->quantity, 1, 0, 'C');
            $this->fpdf->Cell(30, 10, 'R ' . number_format($product->pivot->unit_price, 2), 1, 0, 'R');
            $lineTotal = $product->pivot->quantity * $product->pivot->unit_price;
            $this->fpdf->Cell(30, 10, 'R ' . number_format($lineTotal, 2), 1, 1, 'R');
        }
    }

    private function addTotals()
    {
        $total = $this->sale->calculateTotal();
        $vat = $total * 0.15; // 15% VAT for South Africa
        $grandTotal = $total + $vat;

        $this->fpdf->Ln(5);
        $this->fpdf->SetFont('Arial', 'B', 12);

        $this->fpdf->Cell(130);
        $this->fpdf->Cell(30, 10, 'Subtotal:', 0, 0, 'R');
        $this->fpdf->Cell(30, 10, 'R ' . number_format($total, 2), 0, 1, 'R');

        $this->fpdf->Cell(130);
        $this->fpdf->Cell(30, 10, 'VAT (15%):', 0, 0, 'R');
        $this->fpdf->Cell(30, 10, 'R ' . number_format($vat, 2), 0, 1, 'R');

        $this->fpdf->Cell(130);
        $this->fpdf->Cell(30, 10, 'Total Due:', 0, 0, 'R');
        $this->fpdf->Cell(30, 10, 'R ' . number_format($grandTotal, 2), 0, 1, 'R');
    }

    private function addFooter()
    {
        $this->fpdf->Ln(10);
        $this->fpdf->SetFont('Arial', '', 10);
        $this->fpdf->MultiCell(0, 5, "Payment Terms: Due within 30 days of receipt.\nBank Details: [Your Bank Details]\nReference: Please use invoice number as reference.", 0, 'L');

        $this->fpdf->Ln(10);
        $this->fpdf->Cell(0, 5, 'Thank you for your business!', 0, 1, 'C');
    }
}
