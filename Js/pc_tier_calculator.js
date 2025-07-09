document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll("select");
    const buildButton = document.getElementById("build-pc-btn");
    const totalScoreDisplay = document.getElementById("total-score");
    const chartContainer = document.getElementById("chart-container");

    // Initialize Chart.js
    let pcChart;

    function updateTotalScore() {
        let totalScore = 0;
        selects.forEach(select => {
            const selectedOption = select.options[select.selectedIndex];
            totalScore += parseInt(selectedOption.getAttribute("data-score")) || 0;
        });

        totalScoreDisplay.textContent = `${totalScore}%`;
        chartContainer.style.display = "block"; // Show the chart when button is clicked

        // Determine category
        let low = 0, mid = 0, high = 0;
        if (totalScore <= 30) {
            low = totalScore;
        } else if (totalScore <= 70) {
            mid = totalScore;
        } else {
            high = totalScore;
        }

        // Destroy existing chart if it exists
        if (pcChart) {
            pcChart.destroy();
        }

        const ctx = document.getElementById("pcPerformanceChart").getContext("2d");
        pcChart = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Low-End", "Mid-Range", "High-End"],
                datasets: [{
                    data: [low, mid, high],
                    backgroundColor: ["#dc2626", "#facc15", "#16a34a"],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "70%",
                plugins: {
                    legend: {
                        display: true,
                        position: "bottom",
                    }
                }
            }
        });
    }

    // Show chart only when "Build Your PC" button is clicked
    buildButton.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent any form submission or redirection
        updateTotalScore();
    });
});
