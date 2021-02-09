module.exports = {
    "username": "elastic",
    "repo": "ecs-logging-php",
    "dataSource": "prs",
    "ignoreLabels": [
      "breaking",
      "bug",
      "enhancement",
      "feat",
      "feature",
      "fix",
      "refactor"],
    "ignoreIssuesWith": [
      "automation",
      "ci",
      "developer only"
    ],
    "groupBy": {
        "Breaking changes": ["breaking"],
        "Bug fixes": ["bug", "fix"],
        "Features": ["enhancement", "feature", "feat"]
    },
    "template": {
        commit: ({ message, url, author, name }) => `- [${message}](${url}) - ${author ? `@${author}` : name}`,
        issue: "- {{labels}} {{name}} [{{text}}]({{url}})",
        label: "[**{{label}}**]",
        noLabel: "closed",
        changelogTitle: "# Changelog\n\n",
        release: "## {{release}} ({{date}})\n{{body}}",
        releaseSeparator: "\n---\n\n",
        group: function (placeholders) {
          return '\n#### ' + placeholders.heading + '\n';
        }
    }
}
