document.getElementById('plusButton').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'block';
    document.getElementById('createPostModal').style.display = 'block';
});

// Close modal when close button is clicked
document.getElementById('closeModalButton').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('createPostModal').style.display = 'none';
});

// Close modal when cancel button is clicked
document.getElementById('cancelModalButton').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('createPostModal').style.display = 'none';
});




var selectedImages = []; // Array to store images

// Handle image uploads inside modal for preview
$('#modalImageInputProfile').on('change', function (event) {
    var modalImageUploadDiv = $('#modalImageUpload');
    var files = Array.from(event.target.files);

    files.forEach(function (file, index) {
        selectedImages.push(file); // Store the selected file
        var reader = new FileReader();
        reader.onload = function (e) {
            var imageContainer = $('<div class="image-container"></div>');
            var img = $('<img>').attr('src', e.target.result);
            var deleteIcon = $('<span class="delete-icon">&times;</span>');

            // Store the file index for easy removal
            imageContainer.attr('data-file-index', selectedImages.length - 1);

            deleteIcon.on('click', function () {
                // Remove the image container and the file from the array
                var fileIndex = $(this).parent().data('file-index');
                selectedImages.splice(fileIndex, 1); // Remove the file from the array
                imageContainer.remove(); // Remove the preview
            });

            imageContainer.append(img).append(deleteIcon);
            modalImageUploadDiv.append(imageContainer);
        };
        reader.readAsDataURL(file);
    });

    // Ensure the file input label is always visible
    $('#imageInputLabel').show();
});





$('#createPostForm').on('submit', function (e) {
    e.preventDefault(); // Prevent the default form submission

    var content = $("textarea[name='content']").val();

    if(content){

        // Create a FormData object
        var formData = new FormData(this);

        // Append additional data if needed
        let user_id = localStorage.getItem('user_id');
        formData.append('user_id', user_id); // Replace 1 with the actual user ID

        // Append all images from the selectedImages array to the FormData
        selectedImages.forEach(function (image, index) {
            formData.append('images[]', image);
        });

        // Debug: Log the FormData contents
        for (var pair of formData.entries()) {
            console.log(pair[0]+ ', ' + pair[1]);
        }

        $.ajax({
            url: base_url + 'post-upload.php',
            type: 'POST',
            data: formData,
            processData: false, // Prevent jQuery from automatically transforming the data into a query string
            contentType: false, // Prevent jQuery from setting the content type
            success: function (response) {
                // Handle success
                console.log('Post created successfully:', response);
                // Optionally, hide the modal and clear the form
                $('#createPostModal').hide();
                $('#createPostForm')[0].reset();
                $('#modalImageUpload .image-container').remove(); // Clear uploaded images preview
                selectedImages = []; // Clear the image array

                document.getElementById('modalBackground').style.display = 'none';
                webRoute('social-home');
            },
            error: function (xhr, status, error) {
                // Handle error
                console.error('Failed to create post:', error);
                //alert('Failed to create post. Please try again.');
            }
        });
    } 
});





