@extends('pdf.layout')

@section('head_title')
  {{ $invoice->getNumber() }}
@endsection

@section('body')
    @include('pdf.partials.header', [
        'title' => 'Tax Invoice',
        'data'  => $invoice,
        'pdf'   => $pdf_type,
        'type'  => 'deposit_invoice_paid'
    ])
    @include('pdf.partials.items_table', [
        'data'  => $invoice,
        'pdf'   => $pdf_type,
        'type'  => 'deposit_invoice_paid'
    ])
    @include('pdf.partials.footer', [
        'data'  => $invoice,
        'pdf'   => $pdf_type,
        'type'  => 'deposit_invoice_paid'
    ])
@endsection
