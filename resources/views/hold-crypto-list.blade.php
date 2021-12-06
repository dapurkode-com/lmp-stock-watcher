@extends('adminlte::page')

@section('title', 'My Wallet - Cryptocurrency')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-light">
                <i class="fas fa-coins"></i>
                Cryptocurrency
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">My Wallet</li>
                <li class="breadcrumb-item active">Cryptocurrency</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
    <hold-crypto-list user_id="{{auth()->check() ? auth()->user()->id : null}}"></hold-crypto-list>
@stop
