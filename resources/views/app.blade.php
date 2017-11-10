@extends('layouts.skeleton')

@section('content')
    <div id="app">
        <div class="container">
            <navigation></navigation>
            <transition>
                <router-view></router-view>
            </transition>
        </div>
    </div>
@endsection
