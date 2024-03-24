$(document).ready(function () {
    $(".ekspp").hide();
    $("#modalNewProposal").hide();
    
    if ($("#metode").val() == "eksternal pp" && ($("#sifat").val() == "overhaul" || $("#sifat").val() == "biasa")) {
        $(".ekspp").show();
    }

    $(".select2").select2();

    $(".datepicker").datepicker({
        format:'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd'
    });

    $(".yearpicker").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });


    validasiNewProposal();
    // validasi buat baru proposal
    function validasiNewProposal()
    {
        $("#modalNewProposal").hide();

        var nomorAng = $("#perkiraan_anggaran").val();
        var tahunAng = $("#tahun_anggaran").val();
        var perkiraan = $("#perkiraan").val();

        console.log(nomorAng);
        console.log(tahunAng);
        console.log(perkiraan);

        if (nomorAng != '' && tahunAng != '' && perkiraan != '') {
            $("#modalNewProposal").show();
        }
    }

    $("#tahun_anggaran").change(function(event) {
        validasiNewProposal();
    });

    $("#perkiraan").change(function(event) {
        validasiNewProposal();
    });

    $("#perkiraan_anggaran").keyup(function(event) {
        validasiNewProposal();
    });
    // end:validasi buat baru proposal

    $("#metode").change(function(event) {
        var pelaksana = $(this).val();
        var sifat = $("#sifat").val();
        console.log(sifat);

        if (pelaksana == "eksternal pp" && (sifat == "overhaul" || sifat == "biasa")) {
            $(".ekspp").show();
        }else if (pelaksana == "eksternal pp" && sifat == "emergency") {
            $(".ekspp").hide();
        } else {
            $(".ekspp").hide();
        }
    });

    $("#sifat").change(function(event) {
        var pelaksana = $("#metode").val();
        var sifat = $(this).val();
        console.log(sifat);

        if (pelaksana == "eksternal pp" && (sifat == "overhaul" || sifat == "biasa")) {
            $(".ekspp").show();
        }else if (pelaksana == "eksternal pp" && sifat == "emergency") {
            $(".ekspp").hide();
        } else {
            $(".ekspp").hide();
        }
    });

    $('#modal_proposal').on('shown.bs.modal', function () {
        console.log($("#statuswo").val());
        if ($("#statuswo").val() == '1') {
            $("input[name='perkiraan_proposal']").val($("#perkiraan").val());
        } else {
            $("input[name='perkiraan_proposal']").val($("#perkiraan_revisi").val());
        }
        $("input[name='perkiraan_anggaran_proposal']").val($("#perkiraan_anggaran").val());
        $("input[name='tahun_anggaran_proposal']").val($("#tahun_anggaran").val());

        var fkey = $("#fkey_proposal").val();
        var id = $("#id_wo").val();
        var url = "/proposal/getjson/" + fkey + '/' + id;

        $.ajax({
            type:"GET",
            url: url,
            success: function(data){
                console.log(data.image);
                if (data.result=="success") {
                    $("#imgproposal").html("");
                    $('<img />')
                        .attr('src', "" + data.image + "")
                            .attr('title', data.proposal.nama)
                            .attr('alt', data.proposal.nama)
                            .width('300px').height('300px').appendTo($('#imgproposal')); 
                }
            }
        });
    });

    $("#tgl_mulai_proposal").change(function(event) {
        console.log($("#perkiraan_proposal").html());
        $("#waktu_proposal").val('');

        var date = new Date($(this).val());
        var otherDate = new Date($("#perkiraan_proposal").val());
// console.log(otherDate);
        if (date < otherDate) {
            var result = Math.ceil(Math.abs(date - otherDate) / (1000 * 60 * 60 * 24));
            console.log(result);

            $("#waktu_proposal").val(result);
        } else {
            return false;
        }
    });

    $("#f_proposal").submit(function(e) {   
        e.preventDefault();

        var form = $(this)[0];
        var data = new FormData(this);  
        var urlAction = $(this).attr('action');

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: urlAction,
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 800000,
            success: function (data) {
                if (data.result == 'success') {
                    $('#modal_proposal').modal('hide');

                    $("#c_proposal").prop("disabled", true);

                    Swal.fire(
                      'Sukses',
                      'Data Proposal Berhasil disimpan',
                      'success'
                    );
                }
            },
            error: function (xhr, status, error) {
                // console.log();
                Swal.fire(
                  'Error',
                  'Data gagal disimpan, ' + xhr.responseJSON.message,
                  'error'
                );
            }
        });
    });

    $("#c_proposal").change(function(event) {
        const wo = $("input[name='wo']").val();
        const idwo = $("input[name='id']").val();

        if ($(this).val() != '') {
            $("#modalNewProposal").hide();
        } else {
            validasiNewProposal();
        }

        $("#linkProposal").attr("href", "#");

        $("#linkProposal").attr("target", "_blank");
        $("#linkProposal").attr("href", "/proposal/" + wo + "/" + idwo + "?report=true&idReady=" + $(this).val());
    });
});