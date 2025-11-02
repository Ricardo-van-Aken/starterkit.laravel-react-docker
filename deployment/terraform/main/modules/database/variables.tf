variable "cluster_name" {
  type    = string
  default = "cluster-name"
}

variable "cluster_engine" {
  type    = string
  default = "mysql"
}

variable "cluster_version" {
  type    = string
  default = "8"
}

variable "cluster_size" {
  type    = string
  default = "db-s-1vcpu-1gb"
}

variable "cluster_region" {
  type    = string
  default = "ams3"
}

variable "cluster_node_count" {
  type    = number
  default = 1
}

variable "database_name" {
  type    = string
  default = "database-name"
}

variable "user_name" {
  type    = string
  default = "user-name"
}

variable "project_id" {
  type = string
}
