# NodeType Placeholder

This package introduces a NodeType postprocessor, which parses EEL expression in `placeholder` editor option of nodeType property.

It can be used to show configuration settings in placeholder value.

__Example nodeType configuration:__

```yaml
My.Package:ExampleNode:
  superTypes:
    'Neos.Neos:Content': true
  properties:
    suggestionLimit:
      type: integer
      ui:
        label: i18n
        inspector:
          editorOptions:
            placeholder: '${"Defaults to " + Configuration.setting("My.Package.suggestionLimit")}'
```

