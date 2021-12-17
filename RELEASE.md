# Releasing

## CI based

The release process is also automated in the way any specific commit, from the main branch ideally, can be potentially released, for such it's required the below steps:

1. Checkout the commit to be released
1. Create a tag with the format `[0-9]+.[0-9]+.[0-9]+`, i.e.: `1.0.2`
1. Push the tag to the upstream.
1. Then, the CI pipeline will trigger a release.
1. Wait for an email/slack to confirm the release is ready to be approved, it might take roughly 20 minutes.
1. Login to apm-ci.elastic.co
1. Click on the URL from the email/slack.
1. Click on approve or abort.
1. Then you can go to the `https://packagist.org/packages/elastic/ecs-logging` and [GitHub releases](https://github.com/elastic/ecs-logging-php/releases) to validate that the bundles and release notes have been published.
