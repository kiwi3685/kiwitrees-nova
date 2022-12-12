
ClassicEditor
	.create( document.querySelector(".editor"), {
		licenseKey: "",
	})
	.then( editor => {
		window.editor = editor;
	});