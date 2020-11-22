<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template name="methods">
    <xsl:if test="methods|magicMethods">
      <h3 class="text-info">Methods</h3>
      <table class="table table-bordered">
        <tbody>
          <xsl:for-each select="methods/method|magicMethods/magicMethod">
            <tr>
              <xsl:attribute name="id">
                <xsl:value-of select="@fqn"/>
              </xsl:attribute>
              <td width="1">
                <xsl:value-of select="return/type/def"/>
              </td>
              <td>

                <xsl:value-of select="@name"/>

                <xsl:if test="params/param">
                  <h4>Parameters</h4>
                  <table class="table table-sm table-bordered">
                    <xsl:for-each select="params/param">
                      <tr>
                        <td width="1">
                          <xsl:value-of select="type/def"/>
                        </td>
                        <td>
                          <xsl:value-of select="@name"/>
                        </td>
                      </tr>
                    </xsl:for-each>
                  </table>
                </xsl:if>

                <xsl:call-template name="warnings"/>
                <xsl:value-of select="docblock" disable-output-escaping="yes"/>

                <xsl:call-template name="links"/>
                <xsl:call-template name="todo"/>
              </td>
            </tr>
          </xsl:for-each>
        </tbody>
      </table>
    </xsl:if>
    <xsl:if test="inherited/methods">
      <h4 class="text-muted">Inherited Methods</h4>
      <xsl:call-template name="list">
        <xsl:with-param name="items" select="inherited/methods/method"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>