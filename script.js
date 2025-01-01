function submitVote(contestantCard) {
    const contestantName = contestantCard.querySelector('.contestant-name a').textContent.trim();
    const votes = parseInt(contestantCard.querySelector('.vote-count').textContent);
    const amount = votes * 100; // Assuming 100 per vote

    // First process the payment
    payWithPaystack(amount, function(response) {
        // Only submit to database if payment was successful
        if (response.status === 'success') {
            // Create the data object
            const voteData = {
                contestant_name: contestantName,
                votes: votes,
                amount: amount,
                payment_reference: response.reference // Store payment reference
            };

            // Send the data to the server
            fetch('submit_vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(voteData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Vote submitted successfully!');
                    // Reset vote count
                    contestantCard.querySelector('.vote-count').textContent = '0';
                } else {
                    alert('Error submitting vote: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting vote. Please try again.');
            });
        }
    });
} 