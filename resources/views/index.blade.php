<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>kelompok 8</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./assets/style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e9f5ff;
            margin: 0;
            padding: 0;
        }

        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 3rem;
            color: #007bff;
            font-weight: 700;
            text-align: center;
        }

        .search {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search input {
            font-size: 1.2rem;
            padding: 12px;
            border: 2px solid #007bff;
            border-radius: 50px;
            outline: none;
            width: 80%\;
            transition: border-color 0.3s ease-in-out;
        }

        .search input:focus {
            border-color: #0056b3;
        }

        .suggestions, .sentences, .results {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .suggestion-item, .sentence-item, .result-item {
            padding: 15px;
            margin: 10px 0;
            width: 80%;
            max-width: 600px;
            border: 1px solid #ccc;
            border-radius: 15px;
            background-color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .suggestion-item:hover, .sentence-item:hover, .result-item:hover {
            background-color: #f1f1f1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .result-item {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .result-item h2 {
            font-size: 1.6rem;
            color: #333;
            margin-bottom: 10px;
        }

        .result-item p {
            font-size: 1rem;
            color: #555;
            margin-bottom: 15px;
        }

        .result-item img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .result-item video {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 10px;
        }

        .footer {
            background-color: rgba(44, 62, 80, 0.8);
            color: white;
            padding: 10px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
            font-size: 16px;

        }

        .footer a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: #f0f0f0;
        }

        @media (max-width: 768px) {
            .footer {
                padding: 20px;
                font-size: 14px;
            }

            .search input {
                width: 100%;
            }

            .result-item img,
            .result-item video {
                max-width: 80%;
            }
        }
    </style>
</head>

<body>
    <div id="appStyle" class="container my-5">
        <div class="header">
            <h1><span class="text-success"><i class="fa-brands fa-envira"></i></span>Knowble<span class="text-success">Search</span></h1>
        </div>

        <div class="row">
            <div class="col-lg-5 mx-auto">
                <form id="searchForm" class="search">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="kata" maxlength="100" placeholder="Masukkan Kata Atau Kalimat Yang Ingin Anda cari ">
                    </div>
                </form>
                <div id="suggestions" class="suggestions"></div>
                <div id="sentences" class="sentences"></div>
                <div id="results" class="results"></div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Powered by <a href="#">Kelompok 8</a> &copy; 2024</p>
    </div>

    <!-- JS -->
    <script>
        const searchForm = document.getElementById('searchForm');
        const keywordInput = document.getElementById('kata');
        const suggestionsDiv = document.getElementById('suggestions');
        const sentencesDiv = document.getElementById('sentences');
        const resultsDiv = document.getElementById('results');
        let debounceTimeout;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Create suggestion item
        const createSuggestionItem = (text) => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('suggestion-item');
            itemDiv.textContent = text;
            itemDiv.onclick = () => {
                keywordInput.value = text; // Set input value to selected suggestion
                suggestionsDiv.innerHTML = ''; // Clear suggestions
                fetchSentences(text); // Fetch related sentences
            };
            return itemDiv;
        };

        // Create sentence item
        const createSentenceItem = (text) => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('sentence-item');
            itemDiv.textContent = text;
            itemDiv.onclick = () => {
                sentencesDiv.innerHTML = ''; // Clear sentences
                fetchResults(text); // Fetch related results
            };
            return itemDiv;
        };

        // Create result item (includes image and video)
        const createResultItem = (item) => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('result-item');
            itemDiv.innerHTML = `
                <h2>${item.title}</h2>
                <p>${item.description}</p>
                <img src="/storage/${item.foto}" alt="${item.title}">
                <video controls>
                    <source src="/storage/${item.video}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            `;
            return itemDiv;
        };

        // Fetch suggestions based on keyword input
        const fetchSuggestions = (keyword) => {
            fetch('/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ keyword })
            })
                .then(response => response.json())
                .then(data => {
                    suggestionsDiv.innerHTML = ''; // Clear previous suggestions
                    sentencesDiv.innerHTML = ''; // Clear sentences
                    resultsDiv.innerHTML = ''; // Clear results

                    if (data.suggestions?.kata?.length > 0) {
                        data.suggestions.kata.forEach(item => {
                            suggestionsDiv.appendChild(createSuggestionItem(item));
                        });
                        suggestionsDiv.classList.add('visible');
                    } else {
                        suggestionsDiv.classList.remove('visible');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    suggestionsDiv.classList.remove('visible');
                });
        };

        // Fetch sentences based on selected suggestion
        const fetchSentences = (keyword) => {
            fetch('/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ keyword })
            })
                .then(response => response.json())
                .then(data => {
                    sentencesDiv.innerHTML = ''; // Clear previous sentences
                    resultsDiv.innerHTML = ''; // Clear results

                    if (data.suggestions?.kalimat?.length > 0) {
                        data.suggestions.kalimat.forEach(item => {
                            sentencesDiv.appendChild(createSentenceItem(item));
                        });
                    } else {
                        sentencesDiv.innerHTML = '<p>No sentences found.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    sentencesDiv.innerHTML = '<p>Error fetching sentences.</p>';
                });
        };

        // Fetch results based on selected sentence
        const fetchResults = (sentence) => {
            fetch('/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ keyword: sentence })
            })
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML = ''; // Clear previous results

                    if (data.results?.length > 0) {
                        data.results.forEach(item => {
                            resultsDiv.appendChild(createResultItem(item));
                        });
                    } else {
                        resultsDiv.innerHTML = '<p>No results found.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsDiv.innerHTML = '<p>Error fetching results.</p>';
                });
        };

        // Handle input changes with debounce for suggestions
        keywordInput.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                const keyword = keywordInput.value.trim();
                if (keyword) {
                    fetchSuggestions(keyword);
                } else {
                    suggestionsDiv.classList.remove('visible');
                    sentencesDiv.innerHTML = '';
                    resultsDiv.innerHTML = '';
                }
            }, 300);
        });

        // Prevent default form submission and handle search
        searchForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const keyword = keywordInput.value.trim();
            if (keyword) {
                sentencesDiv.innerHTML = '';
                resultsDiv.innerHTML = '';
                fetchSentences(keyword);
            }
        });
    </script>
</body>

</html>
