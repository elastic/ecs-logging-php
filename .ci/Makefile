SHELL=/bin/bash -o pipefail

CURRENT_UID := $(shell id -u)
CURRENT_GID := $(shell id -g)

.PHONY: help
.DEFAULT_GOAL := help
help:  ## Display this help text
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

.PHONY: prepare
prepare:  ## Prepare the dependencies to run the release
	@docker build --tag gren -f .ci/docker/gren/Dockerfile .

run-%: prepare  ## Run either a release or changelog generation given the GITHUB_TOKEN and TAG_NAME
ifndef GITHUB_TOKEN
	@echo "Please set GITHUB_TOKEN in the environment to generate the changelog"
	exit 1
endif
ifndef TAG_NAME
	@echo "Please set TAG_NAME in the environment to create the changelog"
	exit 1
endif
	@docker run --rm -t \
		--volume "$(PWD)":/app \
		--workdir /app \
		--env GITHUB_TOKEN=$(GITHUB_TOKEN) \
		--env TAG_NAME=$(TAG_NAME) \
		-u $(CURRENT_UID):$(CURRENT_GID) \
		gren $*

.PHONY: release
release: run-release  ## Run a release given the GITHUB_TOKEN and TAG_NAME

.PHONY: changelog
changelog: run-changelog  ## Create the changelog given the GITHUB_TOKEN and TAG_NAME
