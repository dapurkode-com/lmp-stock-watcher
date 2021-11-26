@extends('adminlte::page')

@section('title', 'Watchlist - Cryptocurrency')

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
                <li class="breadcrumb-item">Watchlist</li>
                <li class="breadcrumb-item active">Cryptocurrency</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
    <crypto-list user_id="{{auth()->user()->id}}"></crypto-list>
@stop
