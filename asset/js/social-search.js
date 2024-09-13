var user_id = localStorage.getItem('user_id');
getListUsers(user_id);

function getListUsers(userId) {
    $.ajax({
        url: base_url + "search-users.php",
        type: "POST",
        data: {
            user_id: userId
        },
        success: function(response) {
            var result = ''; // Initialize result as an empty string
            try {
                var data = JSON.parse(response);

                // Debugging: Check the structure of the response
                console.log('Raw response:', response);
                console.log('Parsed data:', data);

                if (data.success) {
                    // Ensure data.users is an array
                    if (Array.isArray(data.users)) {
                        data.users.forEach(function(user) {
                            console.log('User ID:', user.id);
                            console.log('Name:', user.name);
                            console.log('Bio:', user.bio);
                            console.log('Avatar:', user.avatar);
                            console.log('Is Following:', user.is_following);
                            console.log('Is Followed:', user.is_followed);
                            console.log('---'); // Separator for readability

                            // Determine button text and class based on follow status
                            var btnClass = user.is_following ? 'follow-unfollow' : 'follow-following';
                            var btnText = user.is_following ? 'Following' : 'Follow';

                            // Append HTML for each user to the result
                            result += `
                                <div class="search-result-item">
                                    <img src="${base_url + user.avatar}" alt="Avatar" class="avatar">
                                    <div class="result-info">
                                        <div class="result-name">${user.name}</div>
                                        <div class="result-followers">${user.followers_count} followers</div>
                                    </div>
                                    <div class="btn-follow" data-id="${user.id}">
                                        <button class="${btnClass}" data-id="${user.id}">${btnText}</button>
                                    </div>
                                </div>
                            `;
                        });

                        // Update the HTML content of the display list
                        $("#displayListUsers").html(result);

                        // Add event listeners to buttons
                        addEventListeners();
                    } else {
                        console.error('Error: data.users is not an array');
                        $("#displayListUsers").html('No Users Exist');
                    }
                } else {
                    console.error('Error:', data.error); // Handle errors
                    $("#displayListUsers").html('An error occurred');
                }
            } catch (e) {
                console.error('Failed to parse JSON response:', e);
                $("#displayListUsers").html('Failed to load users');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', status, error);
            $("#displayListUsers").html('Failed to load users');
        }
    });
}

function addEventListeners() {
    // Delegate the event to dynamically created elements
    $(document).on('click', '.follow-following', function() {
        var userId = $(this).data('id');
        toggleFollow(userId, 'follow', $(this));
    });

    $(document).on('click', '.follow-unfollow', function() {
        var userId = $(this).data('id');
        toggleFollow(userId, 'unfollow', $(this));
    });
}

function toggleFollow(userId, action, button) {

	var my_id = localStorage.getItem('user_id');
	
    $.ajax({
        url: base_url + "search-users-follow.php",
        type: "POST",
        data: {
            user_id: userId,
            action: action,
            my_id:my_id
        },
        success: function(response) {
            var data = JSON.parse(response);

            if (data.success) {
                console.log('Follow status updated for user ID:', userId);
                
                // Update button text and class based on the action
                if (action === 'follow') {
                    button.removeClass('follow-following').addClass('follow-unfollow').text('Following');
                } else {
                    button.removeClass('follow-unfollow').addClass('follow-following').text('Follow');
                }
            } else {
                console.error('Error:', data.error); // Handle errors
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', status, error);
        }
    });
}
