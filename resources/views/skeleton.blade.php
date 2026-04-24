<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ isset($title) ? $title : 'GESFINANCE' }}</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/font-awesome.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{asset('css/custom.css')}}">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('js/datatables/datatables.min.css') }}" />

    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />

    <!-- Bootstrap JS (après jQuery) -->

    @yield('styles')
</head>


<?php $cur_boutique=session('current_boutique') ?>

<body>
<div class="loading_icon" style="
    position: fixed;
    z-index: 10000;
    Left: calc(50% - 75px);
    top: calc(50% - 75px);
     width: 150px;
     height: 150px;
         /*box-shadow: 0 10px 20px rgba(0, 0, 0, .5);*/
     display: none;
">
    <img src="{{URL::asset('uploads/images/loading.gif')}}" alt="" style="width: inherit;height: inherit">
</div>

<div class="loading-back" style="  position: fixed;
    z-index: 9999;
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    display: none;
    background-color: rgba(255,255,255,0.7);"></div>

<div >
    <img class="back-button" src="{{URL::asset('uploads/images/back_button.png')}}" onclick="history.back()" width="50px" height="50px">
</div>

<div class="nav-side-menu">
    <div class="brand">Bienvenue <a href="#mod_edit_pass" data-toggle="modal"><strong> Hervé</strong></a></div>
    <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>

    <div class="menu-list">

        <ul id="menu-content" class="menu-content collapse out">


            <li onclick='window.location.href=""'>
                <a href="" class="menu-item">
                    <i class="fa fa-home fa-lg"></i> Accueil
                </a>
            </li>


                    <li onclick='window.location.href=""'>
                        <a href="" class="menu-item">
                            <i class="fa fa-shopping-cart fa-lg"></i> Gestion des ventes
                        </a>
                    </li>


            <li onclick='window.location.href=""'>
                <a href="" class="menu-item">
                    <i class="fa fa-square fa-lg"></i> Gestion des stocks
                </a>
            </li>

            <li onclick='window.location.href=""'>
                <a href="" class="menu-item">
                    <i class="fa fa-list fa-lg"></i> Gestion des rapports
                </a>
            </li>


            <li  data-toggle="collapse" data-target="#products" class="collapsed">
                <a href="#" class="menu-item"><i class="fa fa-cog fa-lg"></i> Administration <span class="arrow"></span></a>
            </li>
        {{--    <ul class="sub-menu collapse" id="products" >
                <li onclick='window.location.href="{{route('categorie_management')}}"'><a href="{{route('categorie_management')}}">Catégories</a></li>
                <li onclick='window.location.href="{{route('produit_management')}}"'><a href="{{route('produit_management')}}">Produits</a></li>
                <li onclick='window.location.href="{{route('stock_management')}}"'><a href="{{route('stock_management')}}">Stocks</a></li>
                <li onclick='window.location.href="{{route('pays_management')}}"'><a href="{{route('pays_management')}}">Pays</a></li>
                <li onclick='window.location.href="{{route('client_management')}}"'><a href="{{route('client_management')}}">Clients</a></li>
                <li onclick='window.location.href="{{route('fournisseur_management')}}"'><a href="{{route('fournisseur_management')}}">Fournisseurs</a></li>
                <li onclick='window.location.href="{{route('email_management')}}"'><a href="{{route('email_management')}}">Email de contact</a></li>
                <li onclick='window.location.href="{{route('user_management')}}"'><a href="{{route('user_management')}}">Utilisateurs</a></li>
                <li onclick='window.location.href="{{route('boutique_management')}}"'><a href="{{route('boutique_management')}}">@lang('main.boutique') / @lang('main.magasin')</a></li>

            </ul>
--}}
            <li onclick='window.location.href=""'>
                <a href="" class="menu-item">
                    <i class="fa fa-bar-chart-o fa-lg"></i> Statistiques
                </a>
            </li>





            <li onclick='window.location.href=""'>
                <a href="" class="menu-item" style="color: #1391e8;">
                    <i class="fa fa-user fa-lg"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="container-fluid page" style="padding-right: 0">
    <div class="big-container col-md-12" style="padding-left: 0;padding-right: 0">
        <div class="upper-band" style="overflow-x: auto">

            <div class="content pull-right">
                <a style="display: none;" href="notifications.html" class="upper-band-el"><span class="glyphicon glyphicon-globe"></span> Notifications <i class="badge red">3</i></a>

                <a href="" class="upper-band-el"><span class="fa fa-table"></span> Paramètres</a>
                <p class="conn-as" style="display: none;">
                    Login as <i class="blue">Administrator</i>
                </p>




            </div>




        </div>
        <div class="band-gray">
            @yield('breadcrumb')

        </div>
        <div class="body-head">
            <div class="home body-head-el" style="">
                <span><span class="fa fa-dashboard" style="font-size: 28px;padding-top: 10px"></span> {{isset($big_title)?$big_title:''}} {{isset($title)?' \\ '.$title:''}}</span>
            </div>
            <div class="search body-head-el col-md-6 pull-right" style="display: none">
                <form action="#">
                    <div class="col-md-8 col-sm-7 col-xs-6">
                        <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-search"></span></div>
                            <input type="text" class="form-control">
                        </div>

                    </div>
                    <div class="col-md-4 col-sm-5 col-sm-6">
                        <input type="submit" class="btn btn btn-primary" value="rechercher">

                    </div>
                </form>
            </div>
        </div>

        <div class="container myContainer " style="padding-top: 10px">


            @if(count($errors)>0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>

            @endif
            @if(session('success'))
                <div class="alert alert-success">
                    <strong>Successful</strong>
                </div>
            @endif

            @yield('content')



        </div>


        <div class="footer">
            <p class="text-center" style="color: white;font-weight: 500;"><span class="glyphicon glyphicon-copyright-mark"></span> Copyright 2018</p>
        </div>

    </div>

</div>

</body>

<div id="js-alert" class="bg-primary" style="z-index: 10000000; width: 300px;position: fixed; left: -500px;top: -500px;border-radius: 3px;         box-shadow: 0 15px 25px rgba(0, 0, 0, .7);font-size: 18px;">
    <div class="text-center" style="padding: 10px 15px;margin: auto">
        <strong class="alert-text">Lorem ipsum dolor sit amet</strong>

    </div>

</div>
<div id="js-warning" class="bg-danger" style="z-index: 10000000; width: 300px;position: fixed; left: -500px;top: -500px;border-radius: 3px;         box-shadow: 0 15px 25px rgba(0, 0, 0, .7);font-size: 18px;">
    <div class="text-center text-danger" style="padding: 10px 15px;margin: auto">
        <strong class="alert-text">Lorem ipsum dolor sit amet</strong>

    </div>

</div>

<div class="modal" id="mod_select_boutique">

    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h3 class="modal-title">Sélectionnez @lang('main.boutique') / @lang('main.magasin')</h3>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="col-md-6">
                        <h4>@lang('main.boutique')</h4>
                        <ul id="boutique-list-container" style="font-weight: 600;font-size: 18px;font-family: 'Arial Narrow'">

                        </ul>
                    </div>
                    <div class="col-md-6" >
                        <h4>@lang('main.magasin')</h4>
                        <ul id="magasin-list-container" style="font-weight: 600;font-size: 18px;font-family: 'Arial Narrow'">

                        </ul>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="mod_edit_pass">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">x</button>
                <h3 class="modal-title">Modifier le mot de passe</h3>
            </div>
            <div class="modal-body">
                <ul id="pass-form-errors">

                </ul>
                <form method="post" action="#" id="edit_pass_form">

                    <div class="form-group">
                        <label>Ancien mot de passe</label>
                        <input  type="password" id="amp" name="ancien_mot_de_passe" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input  type="password" id="mp" name="mot_de_passe" minlength="6" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmez le mot de passe</label>
                        <input  type="password" id="mpc" name="mot_de_passe_confirmation" minlength="6" class="form-control" required>
                    </div>

                    <input type="submit" class="btn btn-primary" value="Modifier">
                </form>

            </div>
        </div>

    </div>
</div>


<script src="{{asset('js/Chart.min.js')}}"></script>

<script src="{{ asset('js/jquery.min.js') }}"></script>

<script src="{{ asset('js/bootstrap.min.js') }}"></script>

<!-- <script src="{{ asset('js/datatables/datatables.min.js') }}"></script>  -->

<!-- JSZip (pour export Excel) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<!-- pdfMake (pour export PDF) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Autres scripts personnalisés -->
<script src="{{ URL::asset('js/jquery-ui-1.12.1/jquery-ui.js') }}"></script>
<script src="{{ URL::asset('js/chosen_v1.8.7/chosen.jquery.min.js') }}"></script>
<script src="{{ URL::asset('js/custom.js') }}"></script>
@yield('scripts')
<script>
   /* function get_list_boutique() {
        boutique_cont=$('#boutique-list-container');
        magasin_cont=$('#magasin-list-container');
        boutique_cont.empty();
        magasin_cont.empty();
        $('#mod_select_boutique').modal('show');

        $.ajax({
            {{--url:'{{route('get_boutique_list')}}',--}}
            beforeSend:function () {
                startLoading();
            },
            success:function (data) {
                magasins=data['magasins'];
                boutiques=data['boutiques'];

                mag_html='';
                bou_html='';

                for (i=0;i<magasins.length;i++){
                    {{--mag_html+='<li> <a href="{{route('set_boutique')}}?id='+ magasins[i].id +'"> '+ magasins[i].nom +'</a>  </li>';--}}
                }
                for (i=0;i<boutiques.length;i++){
                    {{--bou_html+='<li> <a href="{{route('set_boutique')}}?id='+ boutiques[i].id +'"> '+ boutiques[i].nom +'</a>  </li>';--}}

                }

                magasin_cont.html(mag_html);
                boutique_cont.html(bou_html);


            },
            error:function () {
                showAlert('Une erreur est survenue',1);
                $('#mod_select_boutique').modal('hide');
            },
            complete:function () {
                stopLoading();
            }
        })
    }
    <!-- DataTables JS -->

    $('#edit_pass_form').on('submit',function (e) {
        e.preventDefault();
        edit_password();

    });
    function edit_password() {
        amp=$('#amp');
        mp=$('#mp');
        mpc=$('#mpc');
        error_cont=$('#pass-form-errors');
        error_cont.empty();

        $.ajax({
            {{--url:'{{route('edit_password')}}',--}}
            type:'POST',
            data:{
                ancien_mot_de_passe: amp.val(),
                mot_de_passe: mp.val(),
                mot_de_passe_confirmation: mpc.val(),
                _token: '{{csrf_token()}}'
            },
            beforeSend:function () {
                startLoading();
            },
            success:function (data) {
                showAlert('Modifié avec succès',0);
                $('#edit_pass_form')[0].reset();
                $('#mod_edit_pass').modal('hide');

            },
            error:function (data) {
                d=data.responseJSON;
                str= d[0];
                showAlert(str,1);
            },
            complete:function () {
                stopLoading();
            }
        })
    }

  {{--@if($cur_boutique==null && Auth::user()->id_boutique==0 )--}}
        $(function () {
        get_list_boutique();
    });
    {{--@endif--}}

    function showAlert(text,type){
        var el;
        if(type===0){
            el=$('#js-alert');
        }else if(type===1)
        {
            el=$('#js-warning');
        }
        var textEl=el.find('.alert-text');
        textEl.html(text);
        el.css('left','calc(50% - 150px)');
        el.css('top','45%');
        el.css('opacity',1);

        /!*setTimeout(function () {
         el.css('opacity',0);

         },10000);*!/
        setTimeout(function () {
            el.css('left','-500px');
        },5000);


    }

    function master_check_change(element) {
        if(element.checked){
            $('.check-list').each(function (e,el) {
                el.checked=true;
            })
        }
        else {
            $('.check-list').each(function (e,el) {
                el.checked=false;
            })
        }

    }

    function sendToDelete(url) {
        var data={},checklist={};
        data['_token']='{{ csrf_token() }}';
        var i=0;
        $('.check-list').each(function (e,el) {
            if(el.checked){
                checklist[i]=el.value;
                i++;

            }


        });
        if(i>0){
            data['check']=checklist;
            postDelete(JSON.stringify(data),url)
        }





    }
    function postDelete(data,url) {
        $.ajax({
            url:url,
            type:'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: data,
            cache: false,
            complete: function (res,stat) {
                location.reload();
            }
        })

    }

    function getContent(url,id) {
        $.ajax({
            url:url,
            type:'POST',
            dataType: 'POST',
            data: "_token={{csrf_token()}}&id="+id,
            cache: false,
            complete: function (res,stat) {
                CKEDITOR.instances.edit_content.setData(res.responseText);

            }
        })

    }
    function startLoading() {
        $('.loading-back').css('display','inline');
        $('.loading_icon').css('display','inline');
    }
    function stopLoading() {
        $('.loading-back').css('display','none');
        $('.loading_icon').css('display','none');
    }*/
</script>


</html>