---
name: Found an issue
about: Something wrong? Let us know.
title: ''
labels: ''
assignees: ''

---

**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Create a Guardian instance with the following configuration:
   ```php
   $guardian = GuardianFactory::make(
       'example-identifier',
       new FileStore('/path/to/cache'),
       [RateLimitRule::allow(100)->perMinute()]
   );
   ```
2. Call the following method:
   ```php
   $result = $guardian->send(function() {
       // Your code here
   });
   ```
3. Observe the error

**Expected behaviour**
A clear and concise description of what you expected to happen.

**Code snippets**
If applicable, add code snippets to help explain your problem.

**Environment:**
 - PHP version: [e.g. 8.2.0]
 - Guardian version: [e.g. 1.0.0]
 - Composer version: [e.g. 2.5.5]
 - Operating System: [e.g. Ubuntu 22.04]
 - Any relevant PHP extensions and their versions

**Additional context**
Add any other context about the problem here. This could include:
- Full error messages or stack traces
- Related configuration files
- Logs (please remove any sensitive information)

**Possible Solution**
If you have any ideas on how to solve the issue, please share them here.
