<?php
    
    // Start session first before any output
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    require 'avatar.php';
    require 'Web/php/connection.php';
    
    // Get service_id from URL parameter
    $serviceId = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
    
    // Debug: Check if service_id is being received
    error_log("Service ID from URL: " . $serviceId);
    
    if (!$serviceId) {
        header('Location: /error?message=Invalid service ID');
        exit();
    }
    
    // Store in session
    $_SESSION['service_id'] = $serviceId;

    try {
    // 1. Fetch service details
    $stmt = $connect->prepare("SELECT * FROM company_services WHERE id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();

    if (!$service) {
    header('Location: /error?message=Service not found');
    exit;
    }

    // 2. Fetch flight types and get minimum price
    $stmt = $connect->prepare("SELECT flight_type_name AS name, price FROM service_flight_types WHERE service_id = ? ORDER
    BY price ASC");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $flightTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $minPrice = !empty($flightTypes) ? $flightTypes[0]['price'] : 0;

    // 3. Fetch office photos
    $stmt = $connect->prepare("SELECT photo_path FROM service_office_photos WHERE service_id = ? ORDER BY photo_order ASC
    LIMIT 4");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();

    $officePhotos = [];
    while ($row = $result->fetch_assoc()) {
    $officePhotos[] = $row['photo_path'];
    }

    // If no photos, use default placeholder
    if (empty($officePhotos)) {
    $officePhotos = ['default-service.jpg'];
    }

    } catch (Exception $e) {
    error_log("Error fetching service data: " . $e->getMessage());
    header('Location: /error?message=Error loading service');
    exit;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($service['service_title'] ?? 'Service Details'); ?></title>
    <link rel="stylesheet" href="Web/css/serviceDesc.css?v=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php
        require('partials/header.php');
        $accType = $_SESSION['acc_type'] ?? 'passenger';
        if ($accType === 'company') {
            require('partials/nav_C.php');
        } else {
            require('partials/nav_P.php');
        }
    ?>
    <div class="main-wrap">
        <!-- Left Section -->
        <div class="left-section">
            <div class="company-title">
                <h1>
                    <?php echo htmlspecialchars($service['service_title'] ?? ''); ?></h1>
            </div>
            <div class="profile-row">
                <img src="" alt="Company Logo" class="profile-pic">
                <div class="profile-info">
                    <span class="profile-name"><?php echo htmlspecialchars($service['company_name'] ?? ''); ?></span>
                    <span class="profile-address"><?php echo htmlspecialchars($service['address'] ?? ''); ?></span>
                    <span class="profile-address"><?php echo htmlspecialchars($service['contact'] ?? ''); ?></span>
                </div>
            </div>
            <div class="rating-row">
                <span class="stars">★★★★☆</span>
                <span class="reviews">4.0 (120 reviews)</span>
            </div>
            <div class="highlight">
                <b>Popular choice!</b> This service has excellent customer satisfaction.
            </div>

            <!-- Enhanced Dynamic Image Slider -->
            <div class="slider-area">
                <div class="main-slider">
                    <?php if (count($officePhotos) > 1): ?>
                    <button class="slider-arrow left" onclick="prevScreenshot()" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <?php endif; ?>
                    <img id="mainImage" src="<?php echo htmlspecialchars($officePhotos[0]); ?>" alt="Service Image">
                    <?php if (count($officePhotos) > 1): ?>
                    <button class="slider-arrow right" onclick="nextScreenshot()" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <?php endif; ?>
                </div>

                <?php if (count($officePhotos) > 1): ?>
                <div class="thumbs-bar screenshots-list" id="screenshotsList">
                    <?php foreach ($officePhotos as $index => $photo): ?>
                    <img src="<?php echo htmlspecialchars($photo); ?>" alt="Screenshot <?php echo $index + 1; ?>"
                        onclick="selectScreenshot(<?php echo $index; ?>)"
                        class="<?php echo $index === 0 ? 'selected' : ''; ?>">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="desc-section">
                <h2>About This Service</h2>
                <div style="white-space: pre-line;">
                    <?php echo htmlspecialchars($service['service_description'] ?? 'No description available.'); ?>
                </div>

                <?php if (!empty($flightTypes)): ?>
                <h3>Available Options</h3>
                <ul>
                    <?php foreach ($flightTypes as $flightType): ?>
                    <li><?php echo htmlspecialchars($flightType['name']); ?> -
                        Rs.<?php echo htmlspecialchars($flightType['price']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <div class="book-box">
                <div class="book-price">Rs.<?php echo htmlspecialchars($minPrice); ?></div>
                <div class="book-desc">
                    <?php echo htmlspecialchars($service['service_title'] ?? 'Service Booking'); ?><br>
                    <span style="font-size:0.98rem;color:rgba(255,255,255,0.8);">Available now • Flexible booking</span>
                </div>
                <!-- Replace the existing book-btn line with this corrected version -->
                <button class="book-btn"
                    onclick="window.location.href='/bookingpassenger?service_id=<?php echo $_SESSION['service_id']; ?>'">Book
                    Now</button>
                <!-- New Chat Button -->
                <button class="chat-btn" onclick="openChatModal()">
                    <i class="fas fa-comments"></i> Send Message
                </button>
            </div>
        </div>
    </div>

    <!-- Chat Modal -->
    <div id="chatModal" class="chat-modal">
        <div class="chat-container">
            <div class="chat-header">
                <h3>Chat with <?php echo htmlspecialchars($service['company_name'] ?? 'Company'); ?></h3>
                <button class="close-chat" onclick="closeChatModal()">&times;</button>
            </div>
            <div class="chat-messages" id="chatMessages">
                <!-- Messages will be loaded here -->
            </div>
            <div class="chat-input-area">
                <input type="text" id="messageInput" placeholder="Type your message..."
                    onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
    // Dynamic slider with PHP-generated photo array
    const screenshots = <?php echo json_encode($officePhotos); ?>;
    let selected = 0;
    const mainImage = document.getElementById('mainImage');
    const thumbsList = document.getElementById('screenshotsList');
    let sliderInterval;

    function updateScreenshot(idx) {
        if (screenshots.length <= 1) return; // Don't update if only one image

        selected = idx;
        mainImage.style.opacity = 0.6;
        setTimeout(() => {
            mainImage.src = screenshots[idx];
            mainImage.style.opacity = 1;
        }, 120);

        if (thumbsList) {
            const thumbs = thumbsList.children;
            for (let i = 0; i < thumbs.length; i++) {
                thumbs[i].classList.toggle('selected', i === idx);
            }
        }
    }

    function selectScreenshot(idx) {
        updateScreenshot(idx);
        resetSliderInterval();
    }

    function prevScreenshot() {
        if (screenshots.length <= 1) return;
        selected = (selected - 1 + screenshots.length) % screenshots.length;
        updateScreenshot(selected);
        resetSliderInterval();
    }

    function nextScreenshot() {
        if (screenshots.length <= 1) return;
        selected = (selected + 1) % screenshots.length;
        updateScreenshot(selected);
        resetSliderInterval();
    }

    function autoSlide() {
        nextScreenshot();
    }

    function resetSliderInterval() {
        if (screenshots.length <= 1) return;
        clearInterval(sliderInterval);
        sliderInterval = setInterval(autoSlide, 5000);
    }

    function bookService(serviceId) {
        // Implement booking logic here
        window.location.href = `/booking?service_id=${serviceId}`;
    }

    // Initialize slider only if multiple images
    if (screenshots.length > 1) {
        updateScreenshot(0);
        resetSliderInterval();

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') prevScreenshot();
            if (e.key === 'ArrowRight') nextScreenshot();
        });

        // Pause auto-slide on hover
        const slider = document.querySelector('.main-slider');
        slider.addEventListener('mouseenter', () => clearInterval(sliderInterval));
        slider.addEventListener('mouseleave', () => resetSliderInterval());
    }

    // Add smooth scroll behavior when booking button is clicked on mobile
    window.addEventListener('resize', function() {
        const bookBox = document.querySelector('.book-box');
        if (window.innerWidth <= 900) {
            // On mobile, keep it fixed at bottom
            bookBox.style.position = 'fixed';
            bookBox.style.bottom = '0';
            bookBox.style.top = 'auto';
            bookBox.style.left = '0';
            bookBox.style.right = '0';
            bookBox.style.width = '100%';
        } else {
            // On desktop, fix it to the right side
            bookBox.style.position = 'fixed';
            bookBox.style.top = '120px';
            bookBox.style.right = '32px';
            bookBox.style.bottom = 'auto';
            bookBox.style.left = 'auto';
            bookBox.style.width = '320px';
        }
    });

    // Trigger resize event on load
    window.dispatchEvent(new Event('resize'));

    let chatInterval;
    const serviceId = <?php echo json_encode($_SESSION['service_id'] ?? 0); ?>;
    const userId = <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;

    function openChatModal() {
        document.getElementById('chatModal').style.display = 'block';
        loadMessages();
        startMessagePolling();
    }

    function closeChatModal() {
        document.getElementById('chatModal').style.display = 'none';
        stopMessagePolling();
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();

        if (!message) return;

        // Add message to UI immediately
        addMessageToUI(message, 'sent', new Date());
        messageInput.value = '';

        // Send to server
        fetch('/api/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    service_id: serviceId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to send message:', data.error);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
            });
    }

    function loadMessages() {
        fetch(`/api/get_messages.php?service_id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const messagesContainer = document.getElementById('chatMessages');
                    messagesContainer.innerHTML = '';

                    data.messages.forEach(message => {
                        addMessageToUI(
                            message.message,
                            message.sender_type === 'passenger' ? 'sent' : 'received',
                            new Date(message.created_at)
                        );
                    });
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    function addMessageToUI(message, type, timestamp) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;

        const timeString = timestamp.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

        messageDiv.innerHTML = `
                <div class="message-bubble">${message}</div>
                <div class="message-time">${timeString}</div>
            `;

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function startMessagePolling() {
        chatInterval = setInterval(loadMessages, 3000); // Poll every 3 seconds
    }

    function stopMessagePolling() {
        if (chatInterval) {
            clearInterval(chatInterval);
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('chatModal');
        if (event.target === modal) {
            closeChatModal();
        }
    }
    </script>
    <?php require('partials/footer.php'); ?>
</body>

</html>