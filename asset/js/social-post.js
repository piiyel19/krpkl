// POST AREA 
// Show modal when plus button is clicked
    


    document.getElementById('viewAllComments').addEventListener('click', function() {
        var hiddenComments = document.getElementById('hiddenComments');
        hiddenComments.style.display = hiddenComments.style.display === 'none' || hiddenComments.style.display === '' ? 'block' : 'none';
        this.textContent = hiddenComments.style.display === 'block' ? 'Hide comments' : 'View all 10 comments';
    });


    // Read More functionality
    var statusText = document.getElementById('statusText');
    var readMore = document.getElementById('readMore');

    readMore.addEventListener('click', function() {
        statusText.innerHTML = 'This is a long status update to demonstrate how to handle longer text in the post. It will be truncated and the user will need to click "Read more" to see the full content. Here is the rest of the content after clicking "Read more".';
    });
// END POST