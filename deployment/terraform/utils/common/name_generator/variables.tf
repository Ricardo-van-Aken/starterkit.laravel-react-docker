variable "length" {
  type        = number
  default     = 6
  description = "Length of the random suffix"
}

variable "name" {
  type    = string
  default = "bucket-name"
}

resource "random_string" "suffix" {
  length  = var.length
  upper   = false
  special = false
  numeric = true
}
