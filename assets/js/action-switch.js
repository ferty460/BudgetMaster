var expTimeItems = document.querySelectorAll('.exp-item');
var expCanvasItems = document.querySelectorAll('.exp-doughnut');
var expStatLists = document.querySelectorAll('.exp-stat-list');

expTimeItems.forEach(function (item) {
    item.addEventListener('click', function () {
        expTimeItems.forEach(function (item) {
            item.classList.remove('active-action');
        });

        this.classList.add('active-action');

        expCanvasItems.forEach(function (item) {
            item.classList.remove('show');
        });

        var canvasId = this.getAttribute('data-canvas');
        var canvas = document.getElementById(canvasId);
        canvas.classList.add('show');

        expStatLists.forEach(function (item) {
            item.classList.remove('active-canvas');
        });

        var statId = this.getAttribute('data-stat');
        var stat = document.getElementById(statId);
        stat.classList.add('active-canvas');
    });
});

var incTimeItems = document.querySelectorAll('.inc-item');
var incCanvasItems = document.querySelectorAll('.inc-doughnut');
var incStatLists = document.querySelectorAll('.inc-stat-list');

incTimeItems.forEach(function (item) {
    item.addEventListener('click', function () {
        incTimeItems.forEach(function (item) {
            item.classList.remove('active-action');
        });

        this.classList.add('active-action');

        incCanvasItems.forEach(function (item) {
            item.classList.remove('show');
        });

        var canvasId = this.getAttribute('data-canvas');
        var canvas = document.getElementById(canvasId);
        canvas.classList.add('show');

        incStatLists.forEach(function (item) {
            item.classList.remove('active-canvas');
        });

        var statId = this.getAttribute('data-stat');
        var stat = document.getElementById(statId);
        stat.classList.add('active-canvas');
    });
});

let incomeBtn = document.getElementById('income-btn');
let expensesBtn = document.getElementById('expenses-btn');
let incomeBlock = document.getElementById('income-block');
let expensesBlock = document.getElementById('expenses-block');
let expForm = document.getElementById("expenses-form");
let incForm = document.getElementById("income-form");

function handleIncomeClick() {
    expensesBtn.classList.remove('active-action');
    incomeBtn.classList.add('active-action');
    expensesBlock.style.display = 'none';
    incomeBlock.style.display = 'block';
    expForm.style.display = 'none';
    incForm.style.display = 'block';
}

function handleExpensesClick() {
    incomeBtn.classList.remove('active-action');
    expensesBtn.classList.add('active-action');
    incomeBlock.style.display = 'none';
    expensesBlock.style.display = 'block';
    incForm.style.display = 'none';
    expForm.style.display = 'block';
}

incomeBtn.addEventListener('click', handleIncomeClick);
expensesBtn.addEventListener('click', handleExpensesClick);