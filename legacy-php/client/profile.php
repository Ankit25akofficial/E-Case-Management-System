<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Legal ChatBot</title>
		<script src="https://cdn.tailwindcss.com"></script>
		<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
	</head>
	<body class="bg-gray-100">
		<div class="flex h-screen">
			<!-- Left Side: Content -->
			<div class="w-1/2 p-8 bg-white">
				<h1 class="text-3xl font-bold text-blue-600 mb-4">Welcome to Legal Assist</h1>
				<p class="text-gray-700 mb-4">
					Your go-to platform for quick legal information and assistance. Whether you need
					information about Indian law, case management, or legal procedures, our chatbot is
					here to help.
				</p>
				<h2 class="text-2xl font-semibold text-blue-600 mt-6 mb-4">Common Legal Queries</h2>
				<ul class="list-disc list-inside text-gray-700">
					<li>What is the punishment for theft in India?</li>
					<li>What are the rights of an arrested person in India?</li>
					<li>What is the legal age for marriage in India?</li>
					<li>How can I file a divorce in India?</li>
				</ul>
				<p class="text-gray-700 mt-6">
					For more complex queries, feel free to ask the chatbot on the right.
				</p>
			</div>

			<!-- Right Side: ChatBot -->
			<div class="w-1/2 p-8 bg-gray-50">
				<h2 class="text-2xl font-bold text-center mb-4">Legal ChatBot</h2>
				<div class="mb-4">
					<input
						type="text"
						id="userInput"
						placeholder="Enter your legal question"
						class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
				</div>
				<button
					onclick="sendMessage()"
					class="w-full bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
					Ask!
				</button>
				<div
					id="response"
					class="mt-5 p-4 bg-white border border-gray-200 rounded-lg shadow-sm"></div>
			</div>
		</div>

		<script>
			// Predefined responses for common queries
			const legalResponses = {
				'What is the punishment for theft in India?':
					'Under Section 378 of the Indian Penal Code, theft is punishable with imprisonment of up to 3 years or a fine, or both.',
				'What are the rights of an arrested person in India?':
					'An arrested person has the right to know the grounds of arrest, the right to consult and be defended by a legal practitioner, and the right to be produced before a magistrate within 24 hours.',
				'What is the legal age for marriage in India?':
					'The legal age for marriage in India is 18 years for females and 21 years for males.',
				'How can I file a divorce in India?':
					'Divorce in India can be filed under various acts such as the Hindu Marriage Act, 1955, the Special Marriage Act, 1954, etc. The process involves filing a petition in the appropriate court, followed by court proceedings.',
			};

			async function sendMessage() {
				const input = document.getElementById('userInput').value.trim();
				const responseDiv = document.getElementById('response');
				if (!input) {
					responseDiv.innerHTML = '<p class="text-red-500">Please enter a message.</p>';
					return;
				}

				// Check if the input matches a predefined query
				if (legalResponses[input]) {
					responseDiv.innerHTML = marked.parse(legalResponses[input]);
					return;
				}

				// Show loading message
				responseDiv.innerHTML = '<p class="text-gray-600">Loading...</p>';

				try {
					// Make API call for non-predefined queries
					const response = await fetch(
						'https://openrouter.ai/api/v1/chat/completions',
						{
							method: 'POST',
							headers: {
								Authorization: 'Bearer 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 ',
								'HTTP-Referer': 'https://www.sitename.com',
								'X-Title': 'SiteName',
								'Content-Type': 'application/json',
							},
							body: JSON.stringify({
								model: 'deepseek/deepseek-r1:free',
								messages: [{ role: 'user', content: input }],
							}),
						},
					);
					const data = await response.json();
					console.log(data);
					const markdownText =
						data.choices?.[0]?.message?.content || 'No response received.';
					responseDiv.innerHTML = marked.parse(markdownText);
				} catch (error) {
					responseDiv.innerHTML = `<p class="text-red-500">Error: ${error.message}</p>`;
				}
			}
		</script>
	</body>
</html>