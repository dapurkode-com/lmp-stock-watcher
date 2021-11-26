@extends('adminlte::page')

@section('title', 'My Wallet - US Stock')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-light">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <my-dashboard user_id="{{auth()->user()->id}}"></my-dashboard>
@stop
