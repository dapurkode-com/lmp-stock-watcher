@extends('adminlte::page')

@section('title', 'Watchlist - Indonesia Stock')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-light">
                <i class="fas fa-landmark"></i>
                Indonesia Stock
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">Watchlist</li>
                <li class="breadcrumb-item">Stock</li>
                <li class="breadcrumb-item active">Indonesia Stock</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
    <idx-stock-list user_id="{{auth()->user()->id}}"></idx-stock-list>
@stop
