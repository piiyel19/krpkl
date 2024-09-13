document.getElementById('editProfileButton').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'block';
    document.getElementById('editProfileModal').style.display = 'block';
});

// Close modal when close button is clicked
document.getElementById('closeModalButton_profile').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('editProfileModal').style.display = 'none';
});

// Close modal when cancel button is clicked
document.getElementById('cancelModalButton_profile').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('editProfileModal').style.display = 'none';
});



document.getElementById('editPasswordButton').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'block';
    document.getElementById('editPasswordModal').style.display = 'block';
});

// Close modal when close button is clicked
document.getElementById('closeModalButton_password').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('editPasswordModal').style.display = 'none';
});

// Close modal when cancel button is clicked
document.getElementById('cancelModalButton_password').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('editPasswordModal').style.display = 'none';
});


document.getElementById('saveEditButton').addEventListener('click', function() {
    var name = $("#userName_update").val();
    var bio = $("#userBio_update").val();
    var userId = localStorage.getItem('user_id');

    $.ajax({
        url: base_url + "profile-update.php",
        type: "POST",
        data: {
            id: userId,
            name:name,
            bio:bio
        },
        success: function(response) {
            var jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;

            if(jsonResponse.success){
                //alert(jsonResponse.message);  // Display the message from the server
                var userId = localStorage.getItem('user_id');
                getProfileDetails(userId);

                localStorage.removeItem('name');
                localStorage.setItem('name', name);


            } else {
                //alert("Error: " + jsonResponse.message);  // Display error message if update failed
            }

            document.getElementById('modalBackground').style.display = 'none';
            document.getElementById('editProfileModal').style.display = 'none';
        }
    });
});


// Handle image uploads inside modal
document.getElementById('modalImageInput').addEventListener('change', function(event) {
    var modalImageUploadDiv = document.getElementById('modalImageUpload');
    Array.from(event.target.files).forEach(function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var imageContainer = document.createElement('div');
            imageContainer.className = 'image-container';

            var img = document.createElement('img');
            img.src = e.target.result;

            var deleteIcon = document.createElement('span');
            deleteIcon.className = 'delete-icon';
            deleteIcon.innerHTML = '&times;';
            deleteIcon.addEventListener('click', function() {
                modalImageUploadDiv.removeChild(imageContainer);
            });

            imageContainer.appendChild(img);
            imageContainer.appendChild(deleteIcon);
            modalImageUploadDiv.appendChild(imageContainer);
        };
        reader.readAsDataURL(file);
    });
});



function triggerImageUpload() {
    document.getElementById('imageInput').click();
}

function updateProfileImage(event) {
    const profileImage = document.getElementById('profileImage');
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = function() {
        profileImage.src = reader.result;
        // Optionally, here you can also send the new image to the server via AJAX
        // Example:
        // uploadProfileImage(reader.result);
        uploadProfileImage(file); // Pass the file to the upload function
    }

    if (file) {
        reader.readAsDataURL(file);
    }
}




// AJAX function to upload profile image
function uploadProfileImage(file) {
    const formData = new FormData();


    var userId = localStorage.getItem('user_id');

    formData.append('avatar', file);
    formData.append('userId', userId);

    // Create an XMLHttpRequest object
    const xhr = new XMLHttpRequest();

    // Open the request
    xhr.open('POST', base_url+'profile-avatar.php', true);

    // Set up a handler for when the request finishes
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log('Profile image updated successfully');
            // You can handle success feedback here
        } else {
            console.error('An error occurred while uploading the image');
            // You can handle error feedback here
        }
    };

    // Send the request with the form data (file)
    xhr.send(formData);
}



var userId = localStorage.getItem('user_id');
getProfileDetails(userId)

function getProfileDetails(userId) {
    $.ajax({
        url: base_url + "profile-details.php",
        type: "POST",
        data: {
            userId: userId
        },
        success: function(response) {
            // Assuming the response is in JSON format
            var userDetails = response.user;
            var followingCount = response.following;
            var followersCount = response.followers;
            
            // Display user details
            console.log("Name: " + userDetails.name);
            console.log("Avatar: " + userDetails.avatar);
            console.log("Bio: " + userDetails.bio);
            
            console.log("Following: " + followingCount);
            console.log("Followers: " + followersCount);
            
            // Example of looping through user details (just for demo purposes)
            // $.each(userDetails, function(key, value) {
            //     console.log(key + ": " + value); // e.g., name: John Doe
            // });

            // You can also update the DOM with these details if needed, like:
            $('#userName').html(userDetails.name);
            $('#profileImage').attr('src', base_url+userDetails.avatar);
            $('#userBio').html(userDetails.bio);
            $('#followingCount').html(followingCount);
            $('#followersCount').html(followersCount);

            $("#totalPost").html(response.totalPost);


            $("#userName_update").val(userDetails.name);
            $("#userBio_update").val(userDetails.bio);
        },
        error: function() {
            console.log("Error fetching user details.");
        }
    });
}








function openTab(tabName) {
    var i;
    var tabContents = document.getElementsByClassName("tab-content");
    var tabButtons = document.getElementsByClassName("tab-button");

    // Hide all tab content
    for (i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = "none";
    }

    // Remove active class from all buttons
    for (i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
    }

    // Show the current tab, and add an active class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    event.currentTarget.classList.add("active");
}

// Default to showing the first tab (text posts)
document.getElementById('textPosts').style.display = 'block';


function default_img_tab() {
    // Hide all tab content
    var tabContents = document.getElementsByClassName("tab-content");
    for (var i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = "none";
    }

    // Remove active class from all buttons
    var tabButtons = document.getElementsByClassName("tab-button");
    for (var i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
    }

    // Show the image tab content and set the image tab button to active
    document.getElementById('imagePosts').style.display = 'block'; // 'imagePosts' should be the ID of the image tab content
    document.getElementById('tabImg').classList.add('active'); // 'imgTabButton' should be the ID of the image tab button
}


function truncateWords(str, wordLimit) {
  // Split the string into words
  let words = str.split(' ');

  // Check if the word count exceeds the limit
  if (words.length > wordLimit) {
    // Join only the first 'wordLimit' words and add '...'
    return words.slice(0, wordLimit).join(' ') + '...';
  }

  // If it's within the limit, return the original string
  return str;
}



myPost(1);
myPost(2);
function myPost(data_type) {
    var user_id = localStorage.getItem('user_id');
    $.ajax({
        url: base_url + "profile-mypost.php",
        type: "POST",
        data: {
            user_id: user_id,
            type: data_type
        },
        success: function(response) {
            // Assuming the response is a JSON string, you need to parse it first
            var data = JSON.parse(response);

            // If the response is an array, you can use forEach
            if (Array.isArray(data)) {

                if(data.length>0){
                    $("#tabAll").show();
                    data.forEach(function(post) {
                        console.log('Post ID:', post.id);
                        console.log('Content:', post.content);
                        console.log('Image URL:', post.img);
                        console.log('Created At:', post.created_at);
                        
                        // You can manipulate the DOM here, for example, append posts to a div
                        if(data_type==1){
                            $("#displayText").append(`
                                <div onclick="detailsPost(${post.id},1);" class="text-post">
                                    ${truncateWords(post.content,20)}
                                </div>
                            `);
                        } else {
                            $("#displayImg").append(`
                                <img onclick="detailsPost(${post.id},2);" src="${base_url+post.img}" alt="Post 1">
                            `);
                        }
                    });
                } else {
                    $("#tabAll").hide();
                }
            } else {
                // If the response is an object (in case of an error or single item), handle it differently
                console.log("Received an object:", data);
                $("#tabAll").hide();
            }
        }
    });
}
