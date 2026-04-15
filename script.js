const prices = {
    'jewelry': 200,
    'cash': 150,
    'electronics': 120
};

function updatePrice() {
    const itemTypeElement = document.getElementById('itemType');
    const priceElement = document.getElementById('priceAmount');
    
    if (itemTypeElement && priceElement) {
        const price = prices[itemTypeElement.value];
        priceElement.textContent = (price ? price : '--') + ' SAR';
    }
}

function isInRiyadh(location) {
    return location.toLowerCase().includes('riyadh');
}

window.onload = function() {
    
    // (Request Details)
    const actionContainer = document.getElementById('actionButtons');
    if (actionContainer) {
        const requestStatus = 'In Transit'; 
        if (requestStatus !== 'Delivered') {
            actionContainer.innerHTML = `
                <button class="role-btn" onclick="goToEdit()" style="background-color: white; color: #5f1428; flex: 1;">Edit</button>
                <button class="role-btn" onclick="confirmDelete()" style="background-color: #fefefe; color: #5f1428; flex: 1;">Delete</button>
            `;
        } else {
            actionContainer.innerHTML = `
                <button class="role-btn" onclick="goToRate()" style="background-color: white; color: #5f1428; width: 100%;">Rate and Review</button>
            `;
            const statusDisp = document.getElementById('displayStatus');
            if(statusDisp) {
                statusDisp.textContent = 'Delivered';
                statusDisp.style.color = '#28a745';
            }
        }
    }

    // (Edit Request After)
    const editForm = document.getElementById('editRequestForm');
    if (editForm) {
        updatePrice(); 
        document.getElementById('itemType').addEventListener('change', updatePrice);
        
        editForm.onsubmit = function(e) {
            e.preventDefault();
            const pickup = document.getElementById('pickupLocation').value;
            const dropoff = document.getElementById('dropoffLocation').value;
            
            if (!isInRiyadh(pickup) || !isInRiyadh(dropoff)) {
                alert("Both locations must be in Riyadh!");
                return;
            }
            alert("✅ Changes Saved!");
            window.location.href = 'request-details.html';
        };
    }
};

// ---  (Global Functions) ---
function goToEdit() { window.location.href = 'EditRequest.html'; }
function goToRate() { window.location.href = 'RateReview.html'; }
function confirmDelete() {
    if (confirm("Are you sure?")) {
        alert("Deleted!");
        window.location.href = 'user.html';
    }
}
function handleDelete() {
    const confirmDelete = confirm("Are you sure you want to delete this request?");
    
    if (confirmDelete) {
        
        alert("✅ Request deleted successfully.");
        
        window.location.href = 'MyRequest.html'; 
    }
}
function goToDetails(status) {
    if (status === 'In Transit') {
        window.location.href = 'RequestDetails.html';
    } else if (status === 'Delivered') {
        window.location.href = 'RequestDetailsAfter.html';
    } else {
        window.location.href = 'RequestDetails.html?status=canceled';
    }
} 