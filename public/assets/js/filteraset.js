$(document).ready(function () {
	$(".select2").select2();
	
	$("#year").datepicker({
	    format: "yyyy",
	    viewMode: "years", 
	    minViewMode: "years"
	});

	/*FILTER BLOCK*/
    $("#monthpicker").datepicker({
        format: "MM-yyyy",
        viewMode: "months", 
        minViewMode: "months"
    });

    $("#reset").on("click", function(e) {
        e.preventDefault();

        $(".select2").val("");
        $(".select2").trigger("change");

        $(".form-control").val("");
        
        $('#tabel').DataTable().ajax.url("/aset/data/data").load();
    })

    $('#instalasi').change(function () {
        $('#lokasi').empty();
        $('#lokasi').append('<option value="">- Pilih Lokasi -</option>');

        $('#ruang').empty();
        $('#ruang').append('<option value="">- Pilih Ruang -</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "/api-general/master/combo-lokasi/" + $(this).val(),
                success: function(result) {
                    $('#lokasi').append(result.data);
                }
            })

            // selectAset();
        }
    });

    $('#lokasi').change(function () {
        $('#ruang').empty();
        $('#ruang').append('<option value="">- Pilih Ruang -</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "/api-general/master/combo-ruang/" + $(this).val(),
                success: function(result) {
                    $('#ruang').append(result.data);
                }
            })

            // selectAset();
        }
    });

    $('#ruang').change(function () {
        if ($(this).val() != '') {
            // selectAset();
            console.log($(this).val());
        }
    });

    $('#kategori').change(function () {
        $('#subkategori').empty();
        $('#subkategori').append('<option value="">- Pilih Sub Kategori -</option>');

        $('#subsubkategori').empty();
        $('#subsubkategori').append('<option value="">- Pilih Sub Sub Kategori -</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "/api-general/master/combo-subkategori/" + $(this).val(),
                success: function(result) {
                    $('#subkategori').append(result.data);
                }
            })
        }
    });

    $('#subkategori').change(function () {
        $('#subsubkategori').empty();
        $('#subsubkategori').append('<option value="">- Pilih Sub Sub Kategori -</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "/api-general/master/combo-subsubkategori/" + $(this).val(),
                success: function(result) {
                    $('#subsubkategori').append(result.data);
                }
            })
        }
    });
/*FILTER BLOCK END*/
});