// Select elements by their IDs
var myPostLikesTab = document.getElementById('myPostLikesTab');
var usersLikeMyPostTab = document.getElementById('usersLikeMyPostTab');
var myPostLikes = document.getElementById('myPostLikes');
var usersLikeMyPost = document.getElementById('usersLikeMyPost');

// Check if all elements are found before adding event listeners
if (myPostLikesTab && usersLikeMyPostTab && myPostLikes && usersLikeMyPost) {
    // Add click event listener to 'myPostLikesTab'
    myPostLikesTab.addEventListener('click', () => {
        // Activate 'myPostLikesTab' and deactivate 'usersLikeMyPostTab'
        myPostLikesTab.classList.add('active');
        usersLikeMyPostTab.classList.remove('active');

        // Show 'myPostLikes' content and hide 'usersLikeMyPost' content
        myPostLikes.style.display = 'block';
        usersLikeMyPost.style.display = 'none';

        // Update aria-selected for accessibility
        myPostLikesTab.setAttribute('aria-selected', 'true');
        usersLikeMyPostTab.setAttribute('aria-selected', 'false');
    });

    // Add click event listener to 'usersLikeMyPostTab'
    usersLikeMyPostTab.addEventListener('click', () => {
        // Activate 'usersLikeMyPostTab' and deactivate 'myPostLikesTab'
        usersLikeMyPostTab.classList.add('active');
        myPostLikesTab.classList.remove('active');

        // Show 'usersLikeMyPost' content and hide 'myPostLikes' content
        usersLikeMyPost.style.display = 'block';
        myPostLikes.style.display = 'none';

        // Update aria-selected for accessibility
        usersLikeMyPostTab.setAttribute('aria-selected', 'true');
        myPostLikesTab.setAttribute('aria-selected', 'false');
    });
} else {
    console.error("One or more elements with the specified IDs were not found in the DOM.");
}
