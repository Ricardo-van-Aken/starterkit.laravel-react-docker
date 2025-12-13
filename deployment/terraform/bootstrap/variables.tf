variable "project_name" {
  type    = string
  default = "project-name"
}

variable "project_description" {
  type    = string
  default = "This is is the default description text."
}

variable "project_environment" {
  type    = string
  default = "Development"
}

variable "domain_name" {
  type    = string
  default = "zeepaardje.xyz"
}

variable "do_token" {
  type      = string
  sensitive = true
}
