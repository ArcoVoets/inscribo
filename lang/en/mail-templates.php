<?php

return [

    'registration_submitted_payment_pending' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Dear "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "We have received your registration. To complete it, you need to pay be "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "expires_at"
                            },
                            "marks": [
                                {
                                    "type": "bold"
                                }
                            ]
                        },
                        {
                            "type": "text",
                            "text": ", otherwise your registration will expire."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Click the button below to see the current status of your registration. You will also find the payment link there."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "status_button"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Kind regards,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

    'waitlisted' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Dear "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "We have received your registration. Unfortunately, there are no more spots available and you have been placed on the waiting list. We will contact you if spots become available."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Click the button below to view the current status of your registration."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "status_button"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Kind regards,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

    'invited_from_waitlist' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Dear "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "A spot has become available for "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        },
                        {
                            "type": "text",
                            "text": ". You have until "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "expires_at"
                            },
                            "marks": [
                                {
                                    "type": "bold"
                                }
                            ]
                        },
                        {
                            "type": "text",
                            "text": " to pay; after that, your registration will expire."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Click the button below to view the current status of your registration. You will also find the payment link there."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "status_button"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Kind regards,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

    'registration_completed' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Dear "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Your payment has been received and your registration is complete. Hereby you receive a confirmation of your registration. You registered using the following details:"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "registration_details"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "The pricing details are as follows:"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "pricing_details"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Click the button below to view the current status of your registration."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "status_button"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Kind regards,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

    'invited_to_register' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Dear "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "You have been invited to pre-register for "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        },
                        {
                            "type": "text",
                            "text": ". "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "expires_at_sentence"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Click the button below to register:"
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "register_button"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Please note: this invitation is personal and can only be used once."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Kind regards,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

];
