$(function () {
    var ajaxResponseBaseTime = 3;
    var ajaxResponseRequestError = "<div class='message error icon-warning'>Desculpe mas não foi possível processar sua requisição...</div>";
    var effecttime = 200;

    /*
     * MOBILE MENU
     */
    $("[data-mobilemenu]").click(function (e) {
        var clicked = $(this);
        var action = clicked.data("mobilemenu");

        if (action === 'open') {
            $(".app_box").slideDown(effecttime);
        }

        if (action === 'close') {
            $(".app_box").slideUp(effecttime);
        }
    });



    // AJAX RESPONSE
    function ajaxMessage(message, time) {
        var ajaxMessage = $(message);

        ajaxMessage.append("<div class='message_time'></div>");
        ajaxMessage.find(".message_time").animate({"width": "100%"}, time * 1000, function () {
            $(this).parents(".message").fadeOut(200);
        });

        $(".ajax_response").append(ajaxMessage);
        ajaxMessage.effect("bounce");
    }

    // AJAX RESPONSE MONITOR
    $(".ajax_response .message").each(function (e, m) {
        ajaxMessage(m, ajaxResponseBaseTime += 1);
    });

    // AJAX MESSAGE CLOSE ON CLICK

    $(".ajax_response").on("click", ".message", function (e) {
        $(this).effect("bounce").fadeOut(1);
    });

    // scroll animate
    $("[data-go]").click(function (e) {
        e.preventDefault();

        var goto = $($(this).data("go")).offset().top;
        $("html, body").animate({scrollTop: goto}, goto / 2);
    });
    /*
        * APP MODAL
        */
    $("[data-modalopen]").click(function (e) {
        var clicked = $(this);
        var modal = clicked.data("modalopen");
        var id = $(this).data("units_id");
        var units = $(this).data("units");
        $(".app_modal").fadeIn(effecttime).css("display", "flex");
        $(modal).fadeIn(effecttime);
        $("#inputID").val(id);
        $("#idUnits").text(" " + units);
        $("html,body").css({"overflow":"hidden"});
    });

    $("[data-modalclose]").click(function (e) {
        $(".app_modal").fadeOut(effecttime).css("display", "none");
        $(".app_modal_box").fadeOut(effecttime).css("display", "none");
        $(".app_modal_box_small").fadeOut(effecttime).css("display", "none");
        $("html,body").css({"overflow":"auto"});
    });

    $("[data-modalsubmit]").on('submit', function(){
        form.trigger("reset");
    });

    $("[data-modalsubmitclose]").click(function () {
        $(".app_modal").fadeOut(effecttime).css("display", "none");
        $("html,body").css({"overflow":"auto"});
    });
    /*
         * FROM BTN
         */
    $("[data-button]").click(function (e) {
        var checkbox = $(this);
        checkbox.parent().find("label").removeClass("check");
        if (checkbox.find("input").is(':checked')) {
            checkbox.addClass("check");
        } else {
            checkbox.removeClass("check");
        }
    });

    /*
     * FROM CHECKBOX
     */
    $("[data-checkbox]").click(function (e) {
        var checkbox = $(this);
        checkbox.parent().find("label").removeClass("check");
        if (checkbox.find("input").is(':checked')) {
            checkbox.addClass("check");
        } else {
            checkbox.removeClass("check");
        }
    });
    /*
       * FADE
       */
    $("[data-fadeout]").click(function (e) {
        var clicked = $(this);
        var fadeout = clicked.data("fadeout");
        $(fadeout).fadeOut(effecttime, function (e) {
            if (clicked.data("fadein")) {
                $(clicked.data("fadein")).fadeIn(effecttime);
            }
        });
    });

    $("[data-fadein]").click(function (e) {
        var clicked = $(this);
        var fadein = clicked.data("fadein");
        $(fadein).fadeIn(effecttime, function (e) {
            if (clicked.data("fadeout")) {
                $(clicked.data("fadeout")).fadeOut(effecttime);
            }
        });
    });

    /*
     * SLIDE
     */
    $("[data-slidedown]").click(function (e) {
        var clicked = $(this);
        var slidedown = clicked.data("slidedown");
        $(slidedown).slideDown(effecttime);
    });

    $("[data-slideup]").click(function (e) {
        var clicked = $(this);
        var slideup = clicked.data("slideup");
        $(slideup).slideUp(effecttime);
    });

    /*
     * TOOGLE CLASS
     */
    $("[data-toggleclass]").click(function (e) {
        var clicked = $(this);
        var toggle = clicked.data("toggleclass");
        clicked.toggleClass(toggle);
        $(this).toggleClass('income expense');
    });

    $("[data-toggleactive]").click(function (e) {
        var clicked = $(this);
        var toggle = clicked.data("toggleactive");
        clicked.toggleClass(toggle);
        $(this).toggleClass('registered confirmed');
    });


    $("#p").click(()=> {
        $("#p").addClass("active")
        $("#i").removeClass("active")

        $(".proprietario").show(800)
        $(".inquilino").hide(800)
    });

    $("#i").click(()=> {
        $("#i").addClass("active")
        $("#p").removeClass("active")

        $(".inquilino").show(800)
        $(".proprietario").hide(800)
    });


    $("[data-user]").on("change", function () {
        var id = $(this).val();
        var load = $(".ajax_load");

        load.fadeIn(200).css("display", "flex");
        $.post("/_cc/erp/corporate/pursuit", {id: id}, function (data) {
            $("#user").html(data);
            load.fadeOut(200);
        });
    });

    $("[data-cookie]").click(()=> {

        $.post("/realizze/erp/accepted", function (data) {
            $(".cookieAlert").fadeOut(200);
        });

    });

    /*
  * PAY
  */

    $("[data-id]").on("change", function(){
        var id = $(this).val();
        var load = $(".ajax_load");

        load.fadeIn(200).css("display", "flex");
        $.post("/_cc/erp/manager/pay", {id: id}, function( data ) {
            $("#pay").html(data);
            load.fadeOut(200);
        });
    });

    $('.window').on('click', function () {
        $('#window').fadeToggle(1000);
    });

    $('.window-n').on('click', function () {
        $('#window-n').fadeToggle(1000);
    });

    $('.window-a').on('click', function () {
        $('#window-a').fadeToggle(1000);
    });

    $('.window-open').on('click', ".condo--name", function () {
            $('#window-c').fadeToggle(1000);
    });

    $('.owl-carousel').owlCarousel({
        loop: true,
        margin: 10,
        responsiveClass: true,
        responsive: {
            0: {
                items: 2,
                nav: true
            },
            500: {
                items: 3,
                nav: true
            },
            600: {
                items: 4,
                nav: true
            },
            700: {
                items: 5,
                nav: true
            },
            800: {
                items: 6,
                nav: true
            },
            900: {
                items: 7,
                nav: true
            },
            1000: {
                items: 8,
                nav: true
            },
            1100: {
                items: 9,
                nav: true
            },
            1200: {
                items: 10,
                nav: true,
                loop: false,
                margin: 20
            }
        }
    })

    /*
     * jQuery MASK
     */
    $(".mask-date").mask('00/00/0000');
    $(".mask-datetime").mask('00/00/0000 00:00');
    $(".mask-month").mask('00/0000', {reverse: true});
    $(".mask-doc").mask('000.000.000-00', {reverse: true});
    $(".mask-pj").mask('00.000.000/0000-00', {reverse: true});
    $(".mask-card").mask('0000  0000  0000  0000', {reverse: true});
    $(".mask-money").mask('000.000.000.000.000,00', {reverse: true, placeholder: "0,00"});
    $(".mask-cep").mask('00000-000', {reverse: true});
    $(".mask-phone").mask('00 0000-0000', {reverse: true});
    $(".mask-cell").mask('00 00000-0000', {reverse: true});
    $(".mask-m2").mask('000.000.000.000.000,00m²', {reverse: true, placeholder: "0,00"});
    /*
        * Units
        */
    var count = 1;
    $('#add-unid').click(function () {
        count++;
        $('#group-unid').append('<label class="label" id="campo' + count + '"><span class="field icon-home">Unidades:</span><span class="line"><input type="text" name="identifier_name[]"placeholder="Numero" required/> <span tooltip="Remover Unidade" flow="up"> <a id="' + count + '" class="red icon-minus-circle rmv icon-notext"></a></span></span></label>');
    });

    $('form').on('click', '.rmv', function () {
        var button_id = $(this).attr("id");
        $('#campo' + button_id + '').remove();
    });


    /*
     * AJAX FORM
     */
    $("form:not('.ajax_off')").submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var load = $(".ajax_load");
        var flashClass = "ajax_response";
        var flash = $("." + flashClass);

        form.ajaxSubmit({
            url: form.attr("action"),
            type: "POST",
            dataType: "json",
            beforeSend: function () {
                load.fadeIn(200).css("display", "flex");
            },
            uploadProgress: function (event, position, total, completed) {
                var loaded = completed;
                var load_title = $(".ajax_load_box_title");
                load_title.text("Enviando (" + loaded + "%)");

                form.find("input[type='file']").val(null);
                if (completed >= 100) {
                    load_title.text("Aguarde, carregando...");
                }
            },
            success: function (response) {
                //redirect
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    load.fadeOut(200);
                }

                //reload
                if (response.reload) {
                    window.location.reload();
                } else {
                    load.fadeOut(200);
                }

                //message
                if (response.message) {
                    if (flash.length) {
                        flash.html(response.message).fadeIn(100).effect("bounce", 300);
                    } else {
                        form.prepend("<div class='" + flashClass + "'>" + response.message + "</div>")
                            .find("." + flashClass).effect("bounce", 300);
                    }
                } else {
                    flash.fadeOut(100);
                }
                load.fadeOut();
            },
            complete: function () {
                if (form.data("reset") === true) {
                    form.trigger("reset");
                }
            },
            error: function () {
                var message = "<div class='message error icon-warning'>Desculpe mas não foi possível processar a requisição. Favor tente novamente!</div>";

                if (flash.length) {
                    flash.html(message).fadeIn(100).effect("bounce", 300);
                } else {
                    form.prepend("<div class='" + flashClass + "'>" + message + "</div>")
                        .find("." + flashClass).effect("bounce", 300);
                }

                load.fadeOut();
            }
        });
    });

    /*
     * APP ON PAID
     */
    $("[data-onpaid]").click(function (e) {
        var clicked = $(this);
        var dataset = clicked.data();

        $.post(clicked.data("unpaid"), dataset, function (response) {
            //reload by error
            if (response.reload) {
                window.location.reload();
            }

            //Balance
            $(".j_total_paid").text("R$ " + response.onpaid.paid);
            $(".j_total_unpaid").text("R$ " + response.onpaid.unpaid);
        }, "json");
    });

    $("[data-onlink]").click(function (e) {
        var clicked = $(this);
        var dataset = clicked.data();

        $.post(clicked.data("onlink"), dataset, function (response) {
            //reload by error
            if (response.reload) {
                window.location.reload();
            }
        }, "json");
    });

    $(document).ready(function() {
        var $search = $("#search");
        var $listItems = $("#list li");

        $search.keyup(function() {
            var searchText = $search.val().toLowerCase();

            $listItems.show().filter(function() {
                return $(this).text().toLowerCase().indexOf(searchText) === -1;
            }).hide();
        });
    });





    /*
     * Upload
     */

    ( function (){
        var input = $( "input[id='doc_cpf']" );
        var textChange = $(".doc_cpf");
        input.change(function (){
            textChange.html(input.val());
        });
    })();

    ( function (){
        var input = $( "input[id='doc_rg']" );
        var textChange = $(".doc_rg");
        input.change(function (){
            textChange.html(input.val());
        });
    })();


    /*
     * IMAGE RENDER
     */
    $("[data-image]").change(function (e) {
        var changed = $(this);
        var file = this;

        if (file.files && file.files[0]) {
            var render = new FileReader();

            render.onload = function (e) {
                $(changed.data("image")).fadeTo(100, 0.1, function () {
                    $(this).css("background-image", "url('" + e.target.result + "')")
                        .fadeTo(100, 1);
                });
            };
            render.readAsDataURL(file.files[0]);
        }
    });

    /*
   * APP INVOICE REMOVE
   */
    $("[data-invoiceremove]").click(function (e) {
        var remove = confirm("ATENÇÃO: Essa ação não pode ser desfeita! Tem certeza que deseja excluir esse lançamento?");

        if (remove === true) {
            $.post($(this).data("invoiceremove"), function (response) {
                //redirect
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            }, "json");
        }
    });

    /*
     * WALLET FILTER
     */
    $(".app_header_widget .wallet").mouseenter(function () {
        $(this).find("ul").slideDown(200);
    }).mouseleave(function () {
        $(this).find("ul").slideUp(200);
    });

    $("[data-walletfilter]").click(function (e) {
        var wallet = $(this).data("wallet");
        var endpoint = $(this).data("walletfilter");

        $(".ajax_load")
            .fadeIn(200)
            .css("display", "flex")
            .find(".ajax_load_box_title")
            .text("Aguarde, abrindo carteira...");

        $.post(endpoint, {wallet: wallet}, function (e) {
            window.location.reload();
        }, "json");
    });

    /*
     * WALLET EDIT
     */
    $("[data-walletedit]").change(function () {
        var wallet = $(this).val();
        var endpoint = $(this).data("walletedit");
        $.post(endpoint, {wallet_edit: wallet}, "json");
    });

    $("[data-post]").click(function (e) {
        e.preventDefault();

        var clicked = $(this);
        var data = clicked.data();
        var load = $(".ajax_load");

        if (data.confirm) {
            var deleteConfirm = confirm(data.confirm);
            if (!deleteConfirm) {
                return;
            }
        }

        $.ajax({
            url: data.post,
            type: "POST",
            data: data,
            dataType: "json",
            beforeSend: function () {
                load.fadeIn(200).css("display", "flex");
            },
            success: function (response) {
                //redirect
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    load.fadeOut(200);
                }

                //reload
                if (response.reload) {
                    window.location.reload();
                } else {
                    load.fadeOut(200);
                }

                //message
                if (response.message) {
                    ajaxMessage(response.message, ajaxResponseBaseTime);
                }
            },
            error: function () {
                ajaxMessage(ajaxResponseRequestError, 5);
                load.fadeOut();
            }
        });
    });



    /*
     * WALLET DELETE
     */
    $(".wallet_action").click(function () {
        $(this).parent().find(".wallet_overlay").fadeIn(200).css("display", "flex");
    });

    $(".wallet_overlay_close").click(function () {
        $(this).parents(".wallet").find(".wallet_overlay").fadeOut(200);
    });

    $("[data-walletremove]").click(function () {
        var wallet = $(this).data("wallet");
        var endpoint = $(this).data("walletremove");

        $(".ajax_load").fadeIn(200).css("display", "flex").find(".ajax_load_box_title").text("Removendo carteira...");
        $.post(endpoint, {wallet_remove: wallet}, function (e) {
            window.location.reload();
        });
    });

});

$(document).ready(function () {

    function limpa_formulário_cep() {
        // Limpa valores do formulário de cep.
        $("#rua").val("");
        $("#bairro").val("");
        $("#cidade").val("");
        $("#uf").val("");
        $("#ibge").val("");
    }

    //Quando o campo cep perde o foco.
    $("#cep").blur(function () {

        //Nova variável "cep" somente com dígitos.
        var cep = $(this).val().replace(/\D/g, '');

        //Verifica se campo cep possui valor informado.
        if (cep != "") {

            //Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;

            //Valida o formato do CEP.
            if (validacep.test(cep)) {

                //Preenche os campos com "..." enquanto consulta webservice.
                $("#rua").val("...");
                $("#bairro").val("...");
                $("#cidade").val("...");
                $("#uf").val("...");
                $("#ibge").val("...");

                //Consulta o webservice viacep.com.br/
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {

                    if (!("erro" in dados)) {
                        //Atualiza os campos com os valores da consulta.
                        $("#rua").val(dados.logradouro);
                        $("#bairro").val(dados.bairro);
                        $("#cidade").val(dados.localidade);
                        $("#uf").val(dados.uf);
                        $("#ibge").val(dados.ibge);
                    } //end if.
                    else {
                        //CEP pesquisado não foi encontrado.
                        limpa_formulário_cep();
                    }
                });
            } //end if.
            else {
                //cep é inválido.
                limpa_formulário_cep();
            }
        } //end if.
        else {
            //cep sem valor, limpa formulário.
            limpa_formulário_cep();
        }
    });
});

$(function () {
    var atual, next, prev;

    $('.next').click(function () {
        atual = $(this).parent();
        next = $(this).parent().next();

        $('.progress_bar li').eq($('.fieldset').index(next)).addClass('active')
        atual.hide(800);
        next.show(800);
    });

    $('.prev').click(function () {
        atual = $(this).parent();
        prev = $(this).parent().prev();

        $('.progress_bar li').eq($('.fieldset').index(atual)).removeClass('active')
        atual.hide(800);
        prev.show(800);
    });
});

$(function () {

    $('input[name="term_search"]').on('keyup', function (e) {
        e.preventDefault();

        var form = $(this).parent('form');
        var link = "/_cc/erp/corporate/controller"

        form.ajaxSubmit({
            url: link,
            data: {s: 'search_product'},
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                if (data.product) {

                    if (!$(form).find('datalist').length) {
                        form.append("<datalist id='j_term_content'></datalist>");
                    }

                    $('datalist').empty();

                    $.each(data.product, function (key, value) {
                        $('datalist').append("<option value='" + value['id'] + " - " + value['first_name'] + " " + value['last_name'] + "'>");
                    });
                }

                if (data.product_clear) {
                    if ($('datalist').length) {
                        $('datalist').remove();
                    }
                }

            }
        });

    });
});

$("input[type=file]").change(function (e) {
    $(this).parents(".uploadFile").find(".filename").text(e.target.files[0].name);
});

$(document).ready(function() {
    $("input, select").on("input", function() {
        var start = $("input[name='start']").val();
        var end = $("input[name='end']").val();
        var before = $("input[name='before']").val();
        var after = $("input[name='after']").val();

        var text = "" + before +  " " + start + " " + after ;

        if(end){
            text += " até " + before +  " " + end + " " + after ;
        }

        $("#exibir-texto").html(text);
    });
});
$(document).ready(function(){
    // Toggle para o botão de navegação
    $('.nav__toggle').click(function(){
        $('.erp_nav').toggleClass('erp_nav_min');

        // Verificar se .erp_nav_min está ativa e esconder os menus .collapse
        if ($('.erp_nav').hasClass('erp_nav_min')) {
            $('.collapse__menu').slideUp(1000);
            $('.collapse__link').removeClass('rotate');
        }
    });

    // LINK ACTIVE
    $('.nav__link').click(function(){
        $('.nav__link').removeClass('active');
        $(this).addClass('active');
    });

    // COLLAPSE MENU
    $('.collapse').click(function(){
        // Verificar se .erp_nav_min está ativa e sair se estiver
        if ($('.erp_nav').hasClass('erp_nav_min')) {
            return;
        }

        const collapseLink = $(this).children('.collapse__link');
        collapseLink.toggleClass('rotate');

        const collapseMenu = $(this).children('.collapse__menu');
        collapseMenu.slideToggle(); // Utiliza o slideToggle para criar o efeito de slide
    });
});

$(document).ready(function() {
    $('.sortable').on('click', function() {
        var column = $(this).data('column');
        var $table = $(this).closest('table');
        var $tbody = $table.find('tbody');
        var rows = $tbody.find('tr').get();

        rows.sort(function(a, b) {
            var A = $(a).children('td').eq(column).text().toUpperCase();
            var B = $(b).children('td').eq(column).text().toUpperCase();
            return A.localeCompare(B);
        });

        if ($(this).hasClass('asc')) {
            rows.reverse();
            $(this).removeClass('asc').addClass('desc');
        } else {
            $(this).removeClass('desc').addClass('asc');
        }

        $.each(rows, function(index, row) {
            $tbody.append(row);
        });
    });
    // Função de pesquisa
    $('#s').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#contact-table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});