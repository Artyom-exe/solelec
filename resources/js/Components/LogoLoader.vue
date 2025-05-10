<template>
    <div class="logo-loader-container">
        <div class="logo-circle">
            <div class="logo-text">
                <span class="letter">S</span>
                <span class="letter o">O</span>
                <span class="letter">L</span>
                <span class="letter">E</span>
                <span class="letter">L</span>
                <span class="letter">E</span>
                <span class="letter">C</span>
            </div>
            <svg class="spinner" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle class="spinner-circle" cx="50" cy="50" r="45" />
            </svg>
        </div>
    </div>
</template>

<style scoped>
.logo-loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem;
}

.logo-circle {
    position: relative;
    width: 140px;
    height: 140px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.logo-text {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 1.5rem;
    display: flex;
    position: relative;
    z-index: 2;
}

.letter {
    color: #0D0703;
}

.o {
    color: #FF8C42;
}

.spinner {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    animation: rotate 1.5s linear infinite;
    z-index: 1;
}

.spinner-circle {
    fill: none;
    stroke: #FF8C42;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-dasharray: 283;
    stroke-dashoffset: 280;
    animation: dash 1.5s ease-in-out infinite;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes dash {
    0% {
        stroke-dashoffset: 280;
    }
    50% {
        stroke-dashoffset: 75;
    }
    100% {
        stroke-dashoffset: 280;
    }
}
</style>
