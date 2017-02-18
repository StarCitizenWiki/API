@extends('layouts.app')
@section('title', 'Star Citizen Wiki API')
@section('lead', 'Dokumentation')

@section('content')
    @include('layouts.heading')
    <div class="container-fluid">
        <div class="row">
                <br>
                @if (Auth::guest())
                    <form class="col-lg-12 mt-3" role="form" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}
                        <div class="input-group input-group-lg offset-sm-4 col-sm-4">
                            <input id="email" type="email" class="center-block form-control input-lg" name="email" value="{{ old('email') }}" required title="Beantrage deinen API-Key" placeholder="E-Mail-Adresse">
                            <span class="input-group-btn">
                                <button class="btn btn-lg btn-primary" type="submit">API-Key beantragen</button>
                            </span>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4 mt-5">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4><i class="fa fa-book"></i> Dokumentation</h4></div>
                    <div class="panel-body mt-3 mr-5">
                        <ul class="list-unstyled">
                            <li>
                                <i class="fa fa-cloud"></i>
                                <span class="ml-2"><a href="">RSI API</a></span>
                            </li>
                            <li>
                                <i class="fa fa-rocket"></i>
                                <span class="ml-2"><a href="">Star Citizen Wiki API<a/></span>
                            </li>
                            <li>
                                <i class="fa fa-image"></i>
                                <span class="ml-2"><a href="">Medien API</a></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-5">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4><i class="fa fa-pencil"></i> Das Projekt</h4></div>
                    <div class="panel-body mt-3 mr-5">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pharetra varius quam sit amet vulputate.
                        Quisque mauris augue, molestie tincidunt condimentum vitae, gravida a libero.
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-5">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4><i class="fa fa-star"></i> Folge uns</h4></div>
                    <div class="panel-body mt-3 mr-5">
                        <ul class="list-unstyled">
                            <li>
                                <i class="fa fa-globe"></i>
                                <span class="ml-2">star-citizen.wiki</span>
                            </li>
                            <li>
                                <i class="fa fa-twitter"></i>
                                <span class="ml-2">@SC_Wiki</span>
                            </li>
                            <li>
                                <i class="fa fa-facebook-square"></i>
                                <span class="ml-2">/StarCitizenWiki</span>
                            </li>
                            <li>
                                <i class="fa fa-building-o"></i>
                                <span class="ml-2">/WIKI</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-5 mt-5">
                <p class="pull-right mt-5"><a href="https://star-citizen.wiki">Star Citizen Wiki</a> &dash; <a href="mailto:api@star-citizen.wiki"><i class="fa fa-envelope-o"></i></a> &dash; &copy; {{ date("Y") }}</p>
            </div>
        </div>

        @if(env('APP_ENV') === 'local')
<<<<<<< HEAD
            @unless(Auth::guest())
                <pre>{{ Auth::user()->api_token }}</pre>
            @endunless
=======
>>>>>>> cb20b7053aa518e5f247b7df31f553e101de49d7
            <div class="row">
                <div class="col-lg-12">
                    <h4>Routen:</h4>
                    @include('snippets.routelist')
                </div>
            </div>
        @endif
    </div>


    <div class="container mt-2">

    </div>
@endsection
