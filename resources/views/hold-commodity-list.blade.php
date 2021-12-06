@extends('adminlte::page')

@section('title', 'My Wallet - Commodity')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-light">
                <i class="fas fa-balance-scale"></i>
                Commodity
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">My Wallet</li>
                <li class="breadcrumb-item active">Commodity</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
    <hold-commodity-list user_id="{{auth()->check() ? auth()->user()->id : null}}"></hold-commodity-list>
@stop
