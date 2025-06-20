<?php 
session_start();
$userid = $_SESSION['user_id'];
$serviceId = $_SESSION['service_id'] ?? 0;
include "Web/php/connection.php";

// Fetch user info
$fill = $connect->prepare("SELECT firstName, lastName, gender, contact, country FROM users_info WHERE user_id = ?");
if (!$fill) {
    die("Prepare failed: " . $connect->error);
}
$fill->bind_param("i", $userid);
$fill->execute();
$result = $fill->get_result()->fetch_assoc();
$Firstname = $result['firstName'] ?? '';
$Lastname = $result['lastName'] ?? '';
$gender = $result['gender'] ?? '';
$contact = $result['contact'] ?? '';
$country = $result['country'] ?? '';


$fillemail2 = $connect->prepare("SELECT email FROM users WHERE id = ?");
if (!$fillemail2) {
    die("Prepare failed: " . $connect->error);
}
$fillemail2->bind_param("i", $userid);
$fillemail2->execute();
$emailResult = $fillemail2->get_result()->fetch_assoc();
$email = $emailResult['email'] ?? '';

$servicefetch = $connect->prepare("SELECT flight_type_name,price FROM service_flight_types WHERE id = ?");
if (!$servicefetch) {
    die("Prepare failed: " . $connect->error);
}
$servicefetch->bind_param("i", $serviceId);
$servicefetch->execute();
$serviceResult = $servicefetch->get_result()->fetch_assoc();
$flightTypeName = $serviceResult['flight_type_name'] ?? '';
$flightTypePrice = $serviceResult['price'] ?? 0;
?>
<body style="background: #fff; min-height: 100vh">
  <div class="main-wrap">
    <div class="booking-form-section">
      <h1 class="booking-title">Book Your Paragliding Adventure</h1>
      <form class="styled-booking-form" id="mainBookingForm" autocomplete="off">
        <div class="form-row">
          <div class="form-group">
            <label for="mainName">Full Name</label>
            <input
              type="text"
              id="mainName"
              name="mainName"
               value ="<?php echo $Firstname . ' ' . $Lastname; ?>"
              required
              placeholder="Your Name"
            />
          </div>
          <div class="form-group">
            <label for="mainEmail">Email Address</label>
            <input
              type="email"
              id="mainEmail"
              name="mainEmail"
              value ="<?php echo $email; ?>"
              required
              placeholder="you@email.com"
            />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="mainPhone">Contact Number</label>
            <input
              type="tel"
              id="mainPhone"
              name="mainPhone"
              value="<?php echo $contact; ?>"
              required
              placeholder="e.g. +977-98XXXXXXXX"
            />
          </div>
          <div class="form-group">
            <label for="mainNationality">Nationality</label>
            <input
              type="text"
              id="mainNationality"
              name="mainNationality"
              value="<?php echo $country; ?>"
              required
              placeholder="Your Country"
            />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="mainDate">Preferred Date</label>
            <input type="date" id="mainDate" name="mainDate"
            value ="<?php echo $country ?>" required />
          </div>
          <div class="form-group">
            <label for="mainPickup">Pickup Location</label>
            <input
              type="text"
              id="mainPickup"
              name="mainPickup"
              required
              placeholder="e.g. Lakeside, Pokhara"
            />
          </div>
          <div class="form-group">
            <label for="mainFlightType">Flight Type</label>
            <input
              type="text"
              id="mainFlightType"
              name="mainFlightType"
              value="<?php echo $flightTypeName ,$flightTypePrice; ?>"
              readonly
              placeholder="Flight Type"
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="mainWeight">Weight (kg)</label>
            <input
              type="number"
              id="mainWeight"
              name="mainWeight"
              min="30"
              max="120"
              required
              placeholder="e.g. 70"
            />
          </div>
          <div class="form-group">
            <label for="mainAge">Age</label>
            <input
              type="number"
              id="mainAge"
              name="mainAge"
              min="10"
              max="80"
              required
              placeholder="e.g. 25"
            />
          </div>
        </div>
            <div class="form-group">
       <label>Gender:</label>
    <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="Male" <?php if ($gender == 'Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if ($gender == 'Female') echo 'selected'; ?>>Female</option>
        <option value="Other" <?php if ($gender == 'Other') echo 'selected'; ?>>Other</option>
    </select><br><br>
        </select>
    </div>

        <div class="form-group">
          <label for="mainNotes">Special Requests / Medical Conditions</label>
          <textarea
            id="mainNotes"
            name="mainNotes"
            rows="3"
            placeholder="Anything we should know?"
          ></textarea>
        </div>
        <div
          class="form-group"
          style="
            margin-top: 18px;
            flex-direction: row;
            align-items: center;
            gap: 10px;
          "
        >
          <input
            type="checkbox"
            id="termsCheck"
            required
            style="width: 18px; height: 18px"
          />
          <label for="termsCheck" style="margin: 0; cursor: pointer"
            >I agree to the
            <a
              href="#"
              id="openTerms"
              style="color: #0d6efd; text-decoration: underline"
              >Terms and Conditions</a
            ></label
          >
        </div>
        <button type="submit" class="main-submit-btn">Book Now</button>
        <div
          id="mainBookingSuccess"
          class="main-success-message"
          style="display: none"
        >
          <span>&#10003;</span> Thank you! Your booking has been received.
        </div>
      </form>
    </div>
  </div>
  <!-- Terms and Conditions Card Overlay -->
  <div id="termsCardOverlay" class="terms-card-overlay" style="display: none">
    <div class="terms-card">
      <button class="close-terms-card" onclick="closeTermsCard()">
        &times;
      </button>
      <h2 class="terms-title">Terms and Conditions</h2>
      <div class="terms-content">
        <ol>
          <li>
            All bookings are subject to weather conditions and availability.
          </li>
          <li>
            Participants must be between 10 and 80 years old and weigh between
            30kg and 120kg.
          </li>
          <li>
            Medical conditions must be disclosed in advance. The company
            reserves the right to refuse service for safety reasons.
          </li>
          <li>
            Bookings can be rescheduled up to 24 hours before the flight time,
            subject to availability.
          </li>
          <li>
            Refunds are only provided if the flight is cancelled due to weather
            or operational reasons.
          </li>
          <li>
            Participants must follow all safety instructions provided by the
            pilot and staff.
          </li>
          <li>
            Personal belongings are the responsibility of the participant.
          </li>
          <li>
            By booking, you agree to our privacy policy and consent to the
            processing of your data for booking purposes.
          </li>
        </ol>
      </div>
    </div>
  </div>
  <style>
    body {
      background: #fff;
      min-height: 100vh;
      margin: 0;
      font-family: "Segoe UI", Arial, sans-serif;
    }
    .main-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 16px;
    }
    .booking-form-section {
      max-width: 1000px;
      margin: 64px auto 0 auto;
      background: linear-gradient(
        135deg,
        #e0e7ff 0%,
        #f0f4ff 60%,
        #e0f7fa 100%
      );
      border-radius: 24px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.13),
        0 1.5px 8px rgba(13, 110, 253, 0.08);
      padding: 48px 80px 40px 80px;
      border: 1.5px solid #e3e8f7;
      position: relative;
      overflow: hidden;
    }
    .booking-form-section::before {
      content: "";
      position: absolute;
      top: -60px;
      left: -60px;
      width: 180px;
      height: 180px;
      background: radial-gradient(
        circle,
        #b6e0fe 0%,
        #e0e7ff 80%,
        transparent 100%
      );
      opacity: 0.35;
      z-index: 0;
    }
    .booking-form-section::after {
      content: "";
      position: absolute;
      bottom: -60px;
      right: -60px;
      width: 180px;
      height: 180px;
      background: radial-gradient(
        circle,
        #a5f3fc 0%,
        #f0f4ff 80%,
        transparent 100%
      );
      opacity: 0.25;
      z-index: 0;
    }
    .booking-title {
      text-align: center;
      color: #0073e6;
      font-size: 2.2rem;
      margin-bottom: 32px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .styled-booking-form {
      display: flex;
      flex-direction: column;
      gap: 0;
    }
    .form-row {
      display: flex;
      gap: 28px;
      margin-bottom: 24px;
    }
    .form-group {
      flex: 1;
      display: flex;
      flex-direction: column;
      margin-bottom: 0;
    }
    .form-group label {
      margin-bottom: 10px;
      color: #333;
      font-weight: 500;
      font-size: 1.05rem;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
      padding: 14px 16px;
      border: 1.5px solid #dbeafe;
      border-radius: 8px;
      font-size: 1.08rem;
      background: #f6fafd;
      transition: border 0.2s;
      outline: none;
      resize: none;
      margin-bottom: 0;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      border-color: #0d6efd;
      background: #fff;
    }
    .form-group textarea {
      width: 100%;
      margin-top: 0;
    }
    .main-submit-btn {
      width: 100%;
      background: #0d6efd;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 15px 0;
      font-size: 1.15rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 18px;
      transition: background 0.2s, box-shadow 0.2s;
      box-shadow: 0 2px 8px rgba(13, 110, 253, 0.08);
    }
    .main-submit-btn:hover,
    .main-submit-btn:focus {
      background: #0a58ca;
      box-shadow: 0 4px 16px rgba(13, 110, 253, 0.13);
    }
    .main-success-message {
      margin-top: 28px;
      color: #007c3c;
      font-size: 1.15rem;
      text-align: center;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    #termsModal .modal-content {
      max-width: 600px;
      width: 96vw;
    }
    .terms-card-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(30, 40, 60, 0.18);
      z-index: 3000;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.2s;
    }
    .terms-card {
      background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
      padding: 38px 32px 28px 32px;
      max-width: 540px;
      width: 96vw;
      position: relative;
      animation: slideDown 0.25s;
    }
    .close-terms-card {
      position: absolute;
      top: 16px;
      right: 18px;
      background: none;
      border: none;
      font-size: 2rem;
      color: #888;
      cursor: pointer;
      transition: color 0.2s;
    }
    .close-terms-card:hover {
      color: #0073e6;
    }
    .terms-title {
      margin-top: 0;
      color: #0073e6;
      font-size: 1.4rem;
      margin-bottom: 18px;
      text-align: center;
    }
    .terms-content {
      max-height: 340px;
      overflow-y: auto;
      color: #222;
      font-size: 1.08rem;
      padding-right: 4px;
    }
    .terms-content ol {
      padding-left: 18px;
    }
    @media (max-width: 900px) {
      .booking-form-section {
        padding: 24px 8px;
        max-width: 98vw;
      }
      .form-row {
        flex-direction: column;
        gap: 0;
      }
    }
    @media (max-width: 600px) {
      .terms-card {
        padding: 16px 4px;
      }
    }
  </style>
  <script>
    // Booking form validation
    const validateBookingForm = (formSelector) => {
      const formElement = document.querySelector(formSelector);
      const validateOptions = [
        {
          attribute: "required",
          isValid: (input) => input.value.trim() === "",
        },
        {
          attribute: "type",
          isValid: (input) => {
            if (input.type === "email") {
              return !/^([^\s@]+)@([^\s@]+)\.[^\s@]{2,}$/.test(input.value);
            }
            if (input.type === "tel") {
              return !/^98\d{8}$/.test(input.value); // Nepal phone format
            }
            if (input.type === "number" && input.name === "mainWeight") {
              return input.value < 30 || input.value > 120;
            }
            if (input.type === "number" && input.name === "mainAge") {
              return input.value < 10 || input.value > 80;
            }
            return false;
          },
        },
        {
          attribute: "date",
          isValid: (input) => {
            if (input.type === "date") {
              const enteredDate = new Date(input.value);
              const today = new Date();
              return isNaN(enteredDate.getTime()) || enteredDate < today;
            }
            return false;
          },
        },
      ];

      const validateSingleInput = (input) => {
        let error = false;
        for (const option of validateOptions) {
          if (
            input.hasAttribute(option.attribute) ||
            option.attribute === "type" ||
            option.attribute === "date"
          ) {
            if (option.isValid(input)) {
              error = true;
              break;
            }
          }
        }
        if (error) {
          input.style.borderColor = "red";
        } else {
          input.style.borderColor = "#0d6efd";
        }
        return !error;
      };

      const setupInputEvents = () => {
        const inputs = Array.from(
          formElement.querySelectorAll("input, textarea, select")
        );
        inputs.forEach((input) => {
          input.addEventListener("input", () => validateSingleInput(input));
          input.addEventListener("blur", () => validateSingleInput(input));
        });
      };

      formElement.setAttribute("novalidate", "");
      formElement.addEventListener("submit", (event) => {
        event.preventDefault();
        const inputs = Array.from(
          formElement.querySelectorAll("input, textarea, select")
        );
        const isValid = inputs.every((input) => validateSingleInput(input));
        if (isValid) {
          formElement.querySelector(".main-submit-btn").style.display = "none";
          document.getElementById("mainBookingSuccess").style.display = "flex";
          setTimeout(() => {
            document.getElementById("mainBookingSuccess").style.display =
              "none";
            formElement.reset();
            formElement.querySelector(".main-submit-btn").style.display = "";
          }, 2200);
        }
      });
      setupInputEvents();
    };

    validateBookingForm("#mainBookingForm");

    document.getElementById("openTerms").onclick = function (e) {
      e.preventDefault();
      document.getElementById("termsCardOverlay").style.display = "flex";
    };
    function closeTermsCard() {
      document.getElementById("termsCardOverlay").style.display = "none";
    }
    document.getElementById("termsCardOverlay").onclick = function (e) {
      if (e.target === this) closeTermsCard();
    };
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeTermsCard();
    });
  </script>

</body>
