if ($('#dt_basic').length) {
	const dt_basic = $('#dt_basic').DataTable({
		language: {
			search: "",
			searchPlaceholder: 'Search',
			paginate: {
				previous: "<i class='fi fi-rr-angle-left'></i>",
				next: "<i class='fi fi-rr-angle-right'></i>",
				first: "<i class='fi fi-rr-angle-double-left'></i>",
				last: "<i class='fi fi-rr-angle-double-right'></i>"
			},
		},
		initComplete: function() {
			$('#dt_basic_wrapper > .row.mt-2.justify-content-between').first().addClass('mx-2 py-2');
		},
	});
}

if ($('#dt_ComplexHeaders').length) {
	const dt_basic = $('#dt_ComplexHeaders').DataTable({
		language: {
			search: "",
			searchPlaceholder: 'Search',
			paginate: {
				previous: "<i class='fi fi-rr-angle-left'></i>",
				next: "<i class='fi fi-rr-angle-right'></i>",
				first: "<i class='fi fi-rr-angle-double-left'></i>",
				last: "<i class='fi fi-rr-angle-double-right'></i>"
			},
		},
		initComplete: function() {
			$('#dt_ComplexHeaders_wrapper > .row.mt-2.justify-content-between').first().addClass('mx-2 py-2');
		},
	});
}

if ($('#dt_AjaxData').length) {
	const dt_basic = $('#dt_AjaxData').DataTable({
		ajax: 'https://gxon.layoutdrop.com/demo/assets/ajax/arrays.txt',
		language: {
			search: "",
			searchPlaceholder: 'Search',
			paginate: {
				previous: "<i class='fi fi-rr-angle-left'></i>",
				next: "<i class='fi fi-rr-angle-right'></i>",
				first: "<i class='fi fi-rr-angle-double-left'></i>",
				last: "<i class='fi fi-rr-angle-double-right'></i>"
			},
		},
		initComplete: function() {
			$('#dt_AjaxData_wrapper > .row.mt-2.justify-content-between').first().addClass('mx-2 py-2');
		},
	});
}

if ($('#dt_ExtraDetailed').length) {
	// Formatting function for row details - modify as you need
	function format(d) {
		// `d` is the original data object for the row
		return (
			'<dl>' +
			'<dt>Full name:</dt>' +
			'<dd>' +
			d.name +
			'</dd>' +
			'<dt>Extension number:</dt>' +
			'<dd>' +
			d.extn +
			'</dd>' +
			'<dt>Extra info:</dt>' +
			'<dd>And any further details here (images etc)...</dd>' +
			'</dl>'
		);
	}

	let table = new DataTable('#dt_ExtraDetailed', {
		ajax: 'https://gxon.layoutdrop.com/demo/assets/ajax/objects.txt',
		columns: [
			{
				className: 'dt-control',
				orderable: false,
				data: null,
				defaultContent: ''
			},
			{ data: 'name' },
			{ data: 'position' },
			{ data: 'office' },
			{ data: 'salary' }
		],
		order: [[1, 'asc']],
		language: {
			search: "",
			searchPlaceholder: 'Search',
			paginate: {
				previous: "<i class='fi fi-rr-angle-left'></i>",
				next: "<i class='fi fi-rr-angle-right'></i>",
				first: "<i class='fi fi-rr-angle-double-left'></i>",
				last: "<i class='fi fi-rr-angle-double-right'></i>"
			},
		},
		initComplete: function() {
			$('#dt_ExtraDetailed_wrapper > .row.mt-2.justify-content-between').first().addClass('mx-2 py-2');
		},
	});

	// Add event listener for opening and closing details
	table.on('click', 'tbody td.dt-control', function (e) {
		let tr = e.target.closest('tr');
		let row = table.row(tr);

		if (row.child.isShown()) {
			// This row is already open - close it
			row.child.hide();
		}
		else {
			// Open this row
			row.child(format(row.data())).show();
		}
	});
}

if ($('#dt_patients2').length) {
	const dt_patients2 = $('#dt_patients2').DataTable({
		processing: true,
		serverSide: true,
		ajax: $('#dt_patients2').data('url'),
		columns: [
			{ data: 'id',           width: '50px' },
			{ data: 'patient',      orderable: true },
			{ data: 'contact',      orderable: false },
			{ data: 'demographics', orderable: false },
			{ data: 'status',       orderable: false },
			{ data: 'action',       orderable: false, searchable: false },
		],
		order: [[0, 'desc']],
		language: {
			search: '',
			searchPlaceholder: 'Search patients...',
			processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			paginate: {
				previous: "<i class='fi fi-rr-angle-left'></i>",
				next:     "<i class='fi fi-rr-angle-right'></i>",
				first:    "<i class='fi fi-rr-angle-double-left'></i>",
				last:     "<i class='fi fi-rr-angle-double-right'></i>",
			},
		},
		initComplete: function () {
			var dtSearch = $('#dt_patients2_wrapper .dt-search').detach();
			$('#dt_patients2_Search').append(dtSearch);
			$('#dt_patients2_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
			$('#dt_patients2_Search .dt-search label').remove();
			$('#dt_patients2_wrapper > .row.mt-2.justify-content-between').first().remove();
		},
	});
}

if ($('#dt_ScrollVertical').length) {
	const dt_basic = $('#dt_ScrollVertical').DataTable({
		paging: false,
		scrollCollapse: true,
		scrollY: '50vh',
		language: {
			search: "",
			searchPlaceholder: 'Search',
			paginate: {
				previous: "<i class='fi fi-rr-angle-left'></i>",
				next: "<i class='fi fi-rr-angle-right'></i>",
				first: "<i class='fi fi-rr-angle-double-left'></i>",
				last: "<i class='fi fi-rr-angle-double-right'></i>"
			},
		},
		initComplete: function() {
			var dtSearch = $('#dt_ScrollVertical_wrapper .dt-search').detach();
			$('#dt_ScrollVertical_Search').append(dtSearch);
			$('#dt_ScrollVertical_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
			$('#dt_ScrollVertical_Search .dt-search label').remove();
			$('#dt_ScrollVertical_wrapper > .row.mt-2.justify-content-between').first().remove();
		},
	});
}



//if ($('#dt_basic').length) {
//	const dt_basic = $('#dt_basic').DataTable({
//		searching: true,
//		pageLength: 10,
//		select: true,
//		lengthChange: true,
//		info: true,
//		paging: true,
//		language: {
//			search: "",
//			searchPlaceholder: 'Search'
//		},
//		initComplete: function() {
//			var dtSearch = $('#dt_basic_wrapper .dt-search').detach();
//			$('#dt_basic_Search').append(dtSearch);
//			$('#dt_basic_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
//			$('#dt_basic_Search .dt-search label').remove();
//			$('#dt_basic_wrapper > .row.mt-2.justify-content-between').first().remove();
//		},
//	});
//}
