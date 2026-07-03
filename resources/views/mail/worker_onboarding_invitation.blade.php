<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Allegiance Heart & Home Care Portal - Worker Onboarding Invitation</title>
	<style>
		.container { font-family: Arial, sans-serif; color: #333; padding: 20px; }
		.btn { display: inline-block; padding: 10px 18px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:6px }
		.meta { font-size: 14px; color: #555 }
		.footer { margin-top: 24px; font-size: 13px; color: #666 }
	</style>
</head>
<body>
	<div class="container">
		<h2>Welcome to the Allegiance Heart &amp; Home Care Portal</h2>
		<p>Dear {{ $worker->first_name }},</p>

		<p>You have been invited to join Allegiance Heart &amp; Home Care as a worker. Please complete your onboarding to gain access to the portal.</p>

		<p><a class="btn" href="{{ $onboardingUrl }}">Start Onboarding</a></p>

		<h4>Your Onboarding Details</h4>
		<ul class="meta">
			<li><strong>Worker ID:</strong> {{ $worker->worker_number }}</li>
			<li><strong>Email:</strong> {{ $worker->email }}</li>
			<li><strong>Phone:</strong> {{ $worker->phone }}</li>
			<li><strong>Invitation Expires:</strong> {{ $expiresAt->format('M d, Y') }}</li>
		</ul>

		<h4>What to expect</h4>
		<ol class="meta">
			<li>Create your account and set up two-factor authentication</li>
			<li>Upload compliance documents (ABN, Police Check, Insurance, Qualifications, etc.)</li>
			<li>Allegiance Heart &amp; Home Care reviews your documents</li>
			<li>Sign required declarations</li>
			<li>Receive approved service categories</li>
			<li>Get assigned to participants</li>
		</ol>

		<p class="footer">If you have any questions or experience any issues during onboarding, please contact our support team.</p>

		<hr>
		<p class="footer">This invitation will expire on <strong>{{ $expiresAt->format('M d, Y') }}</strong>. If you don't complete your onboarding by this date, you'll need to request a new invitation.</p>

		<p class="footer">Best regards,<br>Allegiance Heart &amp; Home Care Team</p>
	</div>
</body>
</html>
