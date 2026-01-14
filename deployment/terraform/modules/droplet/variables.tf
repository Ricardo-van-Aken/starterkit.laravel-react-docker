variable "ssh_key_name" {
  type      = string
  sensitive = true
}

variable "name" {
  type    = string
  default = "droplet-name"
}

variable "region" {
  type    = string
  default = "ams3"
}

variable "size" {
  type    = string
  default = "s-1vcpu-1gb"
}

variable "image" {
  type    = string
  default = "ubuntu-22-04-x64"
}

variable "project_id" {
  type = string
}

data "digitalocean_ssh_key" "me" {
  name = var.ssh_key_name
}
