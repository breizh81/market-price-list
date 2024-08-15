document.getElementById('validateButton').addEventListener('click', function() {
    var fileInput = document.getElementById('fileInput');
    var selectList = document.getElementById('selectList');

    var file = fileInput.files[0];
    var supplier = selectList.value;

    if (!file) {
        alert('No file selected!');
        return;
    }

    if (!supplier) {
        alert('No supplier selected!');
        return;
    }

    var formData = new FormData();
    formData.append('file', file);
    formData.append('supplier', supplier);

    fetch('/import/new', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                alert(data.status);
            } else {
                alert('An unexpected error occurred!');
            }
        })
        .catch(error => console.error('Error:', error));
});
