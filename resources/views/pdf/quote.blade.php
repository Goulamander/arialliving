@extends('pdf.layout')

@section('head_title')
  {{ $quote->getNumber() }}
@endsection

@section('body')
    @include('pdf.partials.header', [
        'title'       => 'Quote',
        'data'        => $quote,
        'pdf'         => 'quote',
        'type'        => 'quote',
        'valid_days'  => $quote_expire_in_days
        
    ])
    @include('pdf.partials.items_table', [
        'data'        => $quote,
        'pdf'         => 'quote',
        'type'        => 'quote',
        'valid_days'  => $quote_expire_in_days
    ])
    @include('pdf.partials.footer', [
        'data'  => $quote,
        'pdf'   => 'quote',
        'type'  => 'quote',
        'valid_days'  => $quote_expire_in_days
    ])
@endsection
