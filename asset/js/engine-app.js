const base_url = 'http://localhost:8055/php/';

$(document).ready(function() {

    // Check if a session exists (e.g., if 'user_id' is stored in localStorage)
    var userId = localStorage.getItem('user_id');

    if (userId) {
        // If session exists, load the club menu
        webRoute('club-menu');
    } else {
        // If no session, load the login page
        $("#content").load("view/login.html");
    }




});


function back_history() {
    // Navigate to the previous page in the browser's history
    // window.history.back();
    // const history = useHistory();
    // history.goBack()
    alert("alert");
}

// Alternatively, if you want to detect browser's actual back button usage
window.addEventListener('popstate', function(event) {
    // Your custom code when the browser back button is pressed
    console.log('Browser back button was pressed.');
    // You can add additional logic here if necessary
});



function webRoute(segment) {

    $("#content").html('');
    if (segment == "club-menu") {
        var name = localStorage.getItem('name'); // Retrieve the name from local storage

        // Load the content and use a callback to display the name after the content is loaded
        $("#content").load("view/club/club-menu.html", function() {
            // This code runs after the content is loaded
            if (name) {
                $("#name_display").html(name); // Display the name in the element with id="name_display"
            } else {
                $("#name_display").html('Guest'); // Fallback if the name is not found
            }
        });


    } else if (segment == "club-event") {
        $("#content").load("view/club/club-event.html");
    } else if (segment == "club-timeline") {
        $("#content").load("view/club/club-timeline.html");
    } else if (segment == "club-details") {
        $("#content").load("view/club/club-details.html");
    } else if (segment === "social-home") {

        $("#content").load("view/social/social-home.html", function() {

        });

    } else if (segment == "social-search") {
        $("#content").load("view/social/social-search.html");
    } else if (segment == "social-like") {
        $("#content").load("view/social/social-like.html");
    } else if (segment == "social-profile") {

        $("#content").load("view/social/social-profile.html", function() {

        });

    } else if (segment == "specific-like") {

        $("#content").load("view/social/social-specific-like.html");
    }
}



function who_like_post(postId) {
    document.getElementById('modalBackground').style.display = 'block';
    document.getElementById('whoLikePostModal').style.display = 'block';


    $.ajax({
        url: base_url + "post-like-who.php",
        type: "POST",
        data: {
            post_id: postId,
        },
        success: function(response) {
            // Parse the response
            var data = JSON.parse(response);

            // Select the container where you want to display the users
            var likeContainer = $("#myPostLikes");

            // Clear the container first
            likeContainer.empty();

            // Check if data is not empty
            if (data.length > 0) {
                // Iterate over the array of users
                data.forEach(function(user) {
                    // Create HTML elements dynamically
                    var userHtml = `
                            <div class="user-item">
					            <div class="user-avatar">
					            	<img src="${base_url}${user.avatar}" alt="${user.name}'s Avatar" class="avatar">
					            </div>
					            <div class="user-details">
					                <div class="user-name">${user.name}</div>
					                <div class="user-date">${user.created_at}</div>
					            </div>

					        </div>
					        <hr>

                        `;

                    // Append the user HTML to the container
                    likeContainer.append(userHtml);
                });
            } else {
                // If no users liked the post, show a message
                likeContainer.append('<p>No likes yet for this post.</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching likes:', error);
        }
    });
}

function close_who_like_post() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('whoLikePostModal').style.display = 'none';
}




function login() {
    $.ajax({
        url: base_url + 'login.php',
        type: 'POST',
        data: {
            username: $('input[name="username"]').val(),
            password: $('input[name="password"]').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                localStorage.setItem('user_id', response.data.user_id);
                localStorage.setItem('username', response.data.username);
                localStorage.setItem('name', response.data.name);
                webRoute('club-menu');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: response.message,
                    confirmButtonText: 'Try Again',
                    width: '300px', // Smaller width for mobile
                    padding: '1rem', // Less padding for a compact look
                    customClass: {
                        popup: 'swal-custom-popup',
                        title: 'swal-custom-title',
                        content: 'swal-custom-content',
                        confirmButton: 'swal-custom-button',
                        icon: 'swal-custom-icon' // Custom icon class
                    }
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.',
                confirmButtonText: 'OK',
                width: '300px', // Smaller width for mobile
                padding: '1rem', // Less padding for a compact look
                customClass: {
                    popup: 'swal-custom-popup',
                    title: 'swal-custom-title',
                    content: 'swal-custom-content',
                    confirmButton: 'swal-custom-button',
                    icon: 'swal-custom-icon' // Custom icon class
                }
            });
        }
    });
}

function logout() {
    localStorage.clear();
    $("#content").load("view/login.html");
}


function detailsPost(post_id,type) {
    localStorage.setItem('post_id', post_id);

    $("#content").load("view/social/social-details-post.html", function() {
        // This code runs after the content is loaded
        if (type==1) {
            $("#back_dtls").html(`<span class="back-button" onclick="back_profile(1);"><i class="fas fa-chevron-left"></i></span>`);
        } else {
            $("#back_dtls").html(`<span class="back-button" onclick="back_profile(2);"><i class="fas fa-chevron-left"></i></span>`);
        }


        // getDetailsPost(post_id);

    });


}


function back_profile(type)
{
    localStorage.removeItem('post_id');

    $("#content").load("view/social/social-profile.html", function() {
        if(type==2){
            default_img_tab();
        }
    });
}