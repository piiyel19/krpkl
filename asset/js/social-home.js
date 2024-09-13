var page = 1; // Initial page number
var limit = 3; // Number of posts to load per request
var loading = false; // To prevent multiple requests
var moreDataAvailable = true; // Flag to check if more data is available

// Initial fetch
fetch_data(page, limit);

function fetch_data(page, limit) {
    if (loading || !moreDataAvailable) {
        return; // Prevent additional requests while loading or if no more data
    }

    loading = true; // Set loading to true to prevent multiple requests

    $.ajax({
        url: base_url + "post-list-all.php",
        type: "POST",
        data: {
            page: page,
            limit: limit
        },
        dataType: 'json',
        success: function(data) {
            if (data.length === 0) {
                moreDataAvailable = false; // No more data available
                $(".div_btn_more").remove(); // Remove button if no more data
            } else {
                // Process and display posts
                data.forEach(function(post) {
                    $("#content_posts").append(renderPost(post));
                    fetch_comment(post.post_id);
                    check_my_like(post.post_id);
                });




                page++; // Increment page number for next fetch

                let next_page = page;

                // Check if more data exists for the next page
                $.ajax({
                    url: base_url + "post-list-all.php",
                    type: "POST",
                    data: {
                        page: page,
                        limit: limit
                    },
                    dataType: 'json',
                    success: function(check) {

                        if (check.length === 0) {
                            moreDataAvailable = false; // No more data available for next page
                            $(".div_btn_more").remove(); // Remove button
                        } else {
                            // Display the 'More' button if more data is available
                            if (!$(".show_more").length) {
                                let btn_add_more = `<div class="content div_btn_more"><button class="show_more">More</button></div>`;
                                $("#content_posts").append(btn_add_more);
                            }

                            // Attach event listener for the 'More' button
                            $(document).off('click', '.show_more').on('click', '.show_more', function() {
                                $(".div_btn_more").remove(); // Remove button
                                fetch_data(next_page, limit);
                            });
                        }
                    },
                    complete: function() {
                        loading = false; // Reset loading flag after processing
                    }
                });
            }
        },
        error: function() {
            loading = false; // Reset loading flag on error
        }
    });
}




function nl2br(str) {
  // Replace newline characters with <br> tags
  return str.replace(/\n/g, '<br>');
}




function renderPost(post) {
    let imagesHtml = "";
    if (post.images && post.images.length > 0) {
        post.images.forEach(function(imageUrl) {
            imagesHtml += `<img src="${base_url + imageUrl}" class="img-post" data-id="${post.post_id}" alt="Post Image">`;
        });
    }

    return `
    <div class="content">
        <div class="card">
            <div class="card-header">
                <img src="${base_url+post.avatar}" alt="User Profile">
                <div class="user-info">
                    <div class="username">${post.name}</div>
                    <div class="time">${post.created_date}</div>
                </div>
            </div>
            <div class="card-body">
                <div class="image-slider">
                    ${imagesHtml}
                </div>
                <div class="status-text">${nl2br(post.content)}</div>
            </div>
            <div class="card-footer">
                <div class="interaction-row">
                    <i class="far fa-heart like-button" data-id="${post.post_id}"></i>
                    <span class="like-count" id="like_${post.post_id}" onclick="who_like_post(${post.post_id});">${post.likes} likes</span>
                </div>
                <!-- Comment Section -->
                <div class="comment-section">
                    <div id="display_comment_${post.post_id}"></div>
                    
                    <!-- Comment Input -->
                    <div class="comment-input">
                        <input type="text" class="comment_user" data-id="${post.post_id}" placeholder="Add a comment...">
                        <button type="button" class="comment_btn" data-id="${post.post_id}">Post</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
`;


}

// Attach click event listener to the button
$(document).on('click', '.comment_btn', function() {
    // Get the post ID from the button's data-id attribute
    let postId = $(this).data('id');

    // Get the comment input field associated with this post using the same data-id
    let commentInput = $(`.comment_user[data-id="${postId}"]`);

    // Retrieve the comment entered by the user
    let comment = commentInput.val();

    // Log the values for debugging purposes
    console.log("Post ID:", postId);
    console.log("User Comment:", comment);

    // Check if the comment is not empty before proceeding
    if (comment.trim() !== '') {
        // Here you can send the post ID and comment to the server using AJAX

        var userId = localStorage.getItem('user_id');

        $.ajax({
            url: base_url + "post-submit-comment.php",
            type: "POST",
            data: {
                post_id: postId,
                user_id: userId,
                content: comment
            },
            success: function(response) {
                // Handle success (e.g., display the new comment, clear input)
                commentInput.val(''); // Clear input after posting
                console.log("Comment posted successfully.");

                fetch_comment(postId);
            },
            error: function() {
                console.log("Error posting comment.");
            }
        });
    } else {
        //alert("Please enter a comment before posting.");
        Swal.fire({
            // icon: 'error',
            // title: 'Comment Requirement Notice',
            text: 'Please write a comment before submitting your comment.',
            confirmButtonText: 'OK',
            width: '300px', // Smaller width for mobile
            padding: '1rem', // Less padding for a compact look
            customClass: {
                popup: 'swal-custom-popup',
                // title: 'swal-custom-title',
                content: 'swal-custom-content',
                confirmButton: 'swal-custom-button',
                icon: 'swal-custom-icon' // Custom icon class
            }
        });
    }
});



function fetch_comment(postId) {
    var userId = localStorage.getItem('user_id');

    $.ajax({
        url: base_url + "post-comment.php",
        type: "POST",
        data: {
            post_id: postId
        },
        success: function(response) {
            // Check if the response is a string and needs to be parsed as JSON
            if (typeof response === 'string') {
                response = JSON.parse(response);
            }

            // Clear the container before appending new comments
            $("#display_comment_" + postId).html('');

            var totalComments = response.comments.length;

            // Append "View all" or "Hide all" comments button if more than 2 comments
            if (totalComments > 2) {
                var toggle_button = `<div class="view-all-comments" id="viewAllComments_${postId}" data-id="${postId}" style="cursor:pointer;">
                                View all ` + totalComments + ` comments
                            </div>`;
                $("#display_comment_" + postId).append(toggle_button);
            }

            if (response.status === 'success') {
                response.comments.forEach((comment, index) => {


                    var btn_delete = ``;
                    if (comment.user_id == userId) {
                        var btn_delete = `<span style="float:right;" class="delete-comments" data-id="${comment.id}"><i class="fas fa-eraser" id="delete_id_${comment.id}" data-id="${postId}" style="color:#9b92b2;"></i></span>`;
                    }

                    // Create the HTML structure for each comment
                    let commentHtml = `
                <div class="comment" id="comment_${index}_${postId}" style="display:${index >= 2 ? 'none' : ''};">
                    <img src="${base_url + comment.avatar}" alt="User ${comment.user_id}">
                    <div class="comment-content">
                        <strong>${comment.name}:</strong> ${comment.content}
                        <p style="color:#9b8585; font-size:12px;">${comment.created_date}</p>
                        ${btn_delete}
                    </div>
                </div>
            `;

                    // Append the generated HTML to the container
                    $("#display_comment_" + postId).append(commentHtml);
                });


            } else {
                $("#display_comment_" + postId).append('<p>No comments found.</p>');
            }
        },
        error: function() {
            $("#display_comment_" + postId).append('<p>Error loading comments.</p>');
        }
    });
}


$(document).on('click', '.view-all-comments', function() {
    // Get the data-id from the clicked element
    var postId = $(this).data('id');

    // Display the data-id in an alert
    var commentsHidden = $(`#display_comment_${postId} .comment`).slice(2).is(':hidden');

    if (commentsHidden) {
        // Show all hidden comments
        $(`#display_comment_${postId} .comment`).slice(2).show();
        // Update the button to "Hide all"
        $(this).text('Hide comments');
    } else {
        // Hide comments again, show only the first 2
        $(`#display_comment_${postId} .comment`).slice(2).hide();
        // Update the button to "View all"
        $(this).text('View all  comments');
    }

    // You can now use postId for further actions
});


$(document).on('click', '.like-button', function() {
    // Get the data-id from the clicked element
    var postId = $(this).data('id');

    // Check if the like button already has the 'fas' class (which means it's liked)
    if ($(this).hasClass('fas')) {
        // If it's already liked, remove the 'fas' and add 'far' to show the unliked state
        $(this).removeClass('fas').addClass('far');
        // Optionally, you can remove the color red if applied
        $(this).css('color', '');

        var tick = 0;

    } else {
        // If it's not liked, replace 'far' with 'fas' to show the liked state
        $(this).removeClass('far').addClass('fas');
        // Change the color to red
        $(this).css('color', 'red');

        var tick = 1;
    }

    // Optional: You can also handle the logic to save the like state in the database here.
    saveLike(tick, postId);
    //alert(postId);
});



$(document).on('dblclick', '.img-post', function() {
    var postId = $(this).data('id');

    // Use postId to target the correct like button related to this post
    var likeButton = $('.like-button[data-id="' + postId + '"]');

    if (likeButton.hasClass('fas')) {
        // If it's already liked, remove the 'fas' and add 'far' to show the unliked state
        likeButton.removeClass('fas').addClass('far');
        // Optionally, you can remove the color red if applied
        likeButton.css('color', '');

        var tick = 0;

    } else {
        // If it's not liked, replace 'far' with 'fas' to show the liked state
        likeButton.removeClass('far').addClass('fas');
        // Change the color to red
        likeButton.css('color', 'red');

        var tick = 1;
    }

    saveLike(tick, postId);
});



function saveLike(tick, postId) {
    var userId = localStorage.getItem('user_id');

    $.ajax({
        url: base_url + "post-like.php",
        type: "POST",
        data: {
            post_id: postId,
            tick: tick,
            user_id: userId
        },
        success: function(response) {

            var data = JSON.parse(response);

            // Check if like exists
            if (data.status) {
                getTotalLike(postId);
            }

        },
        error: function() {

        }
    });
}




function getTotalLike(postId) {
    $.ajax({
        url: base_url + "post-like-all.php",
        type: "POST",
        data: {
            post_id: postId
        },
        success: function(response) {

            // Parse the response
            var data = JSON.parse(response);

            // Check if like exists
            if (data.like_count) {
                $("#like_" + postId).html(data.like_count + ' likes');
            } else {
                $("#like_" + postId).html('0 likes');
            }

        },
        error: function() {

        }
    });
}



function check_my_like(postId) {
    var userId = localStorage.getItem('user_id');

    $.ajax({
        url: base_url + "post-mylike.php",
        type: "POST",
        data: {
            post_id: postId,
            user_id: userId
        },
        success: function(response) {

            // Parse the response
            var data = JSON.parse(response);

            var likeButton = $('.like-button[data-id="' + postId + '"]');

            // Check if like exists
            if (data.like_exists) {
                // If the user liked the post
                console.log("User has liked the post.");
                // You can update the UI to reflect the "liked" status, e.g., change the like button color

                // If it's not liked, replace 'far' with 'fas' to show the liked state
                likeButton.removeClass('far').addClass('fas');
                // Change the color to red
                likeButton.css('color', 'red');



            } else {
                // If the user hasn't liked the post
                console.log("User has not liked the post.");
                // You can update the UI to show the "unliked" status
                likeButton.removeClass('fas').addClass('far');
                // Optionally, you can remove the color red if applied
                likeButton.css('color', '');

            }

        },
        error: function() {

        }
    });
}







$(document).on('click', '.delete-comments', function() {
    // Get the data-id from the clicked element
    var id = $(this).data('id');

    $("input[name='id_delete']").val(id);


    var postId = $("#delete_id_" + id).data('id');

    $("input[name='id_post']").val(postId);

    //confirmationModal_post
    document.getElementById('modalBackground').style.display = 'block';
    document.getElementById('confirmationModal_post').style.display = 'block';

});



//confirmNoButton_post
//confirmYesButton


document.getElementById('confirmNoButton_post').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('confirmationModal_post').style.display = 'none';
});


document.getElementById('confirmYesButton_post').addEventListener('click', function() {
    document.getElementById('modalBackground').style.display = 'none';
    document.getElementById('confirmationModal_post').style.display = 'none';


    var id = $("input[name='id_delete']").val();
    var postId = $("input[name='id_post']").val();

    var userId = localStorage.getItem('user_id');

    $.ajax({
        url: base_url + "post-delete-comment.php",
        type: "POST",
        data: {
            post_id: postId,
            user_id: userId,
            id: id
        },
        success: function(response) {
            // Parse the response as JSON if it comes as a string
            try {
                var jsonResponse = JSON.parse(response);
            } catch (e) {
                console.log("Error parsing JSON response:", e);
                return;
            }

            // Check if the success property is true
            if (jsonResponse.success === true) {
                console.log("Comment deleted successfully.");
                //alert("Comment deleted successfully.");
                fetch_comment(postId);  // Refresh the comments after deletion
            } else {
                console.log("Failed to delete comment:", jsonResponse.message);
                //alert("Failed to delete comment.");
            }
        },
        error: function() {
            console.log("Error posting comment.");
        }
    });
});