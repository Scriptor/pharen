(route "said"
    (flow
        (form (input "foo"))
        (link "click here")
        (p "hello " ($ session "foo")))

